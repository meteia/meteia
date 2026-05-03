<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandMessageHandler;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyCommandInbox implements CommandInbox
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
    ) {}

    #[\Override]
    public function subscribe(string $commandClassName, CommandMessageHandler $handler): void
    {
        $this->channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $queueName = str_replace('\\', '.', $commandClassName);
        $this->log->info('Subscribing Command Handler', ['queue' => $queueName]);

        $this->channel->queueDeclare(queue: $queueName, durable: true);
        $this->channel->queueBind(queue: $queueName, exchange: $this->exchangeName, routingKey: $queueName);

        $this->channel->consume(function (Message $message, Channel $channel, Client $bunny) use (
            $commandClassName,
            $queueName,
            $handler,
        ): void {
            $commandId = CommandId::fromToken($message->headers['message-id']);
            $correlationId = CorrelationId::fromToken($message->headers['correlation-id']);
            $causationId = CausationId::fromToken($message->headers['causation-id']);
            $processId = ProcessId::fromToken($message->headers['process-id']);
            $this->log->info('Received Command', [
                'queueName' => $queueName,
                'commandId' => $commandId,
                'correlationId' => $correlationId,
                'causationId' => $causationId,
                'processId' => $processId,
            ]);

            try {
                $command = $this->serializer->deserialize($message->content, $commandClassName, 'json');
                $handler->handle($command, $commandId, $correlationId, $causationId, $processId);
                $channel->ack($message);
            } catch (\Throwable $t) {
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), ['queueName' => $queueName]);
            }
        }, $queueName);
    }

    #[\Override]
    public function run(): void
    {
        $this->loop->runUntilShutdown('CommandWorkers.Shutdown');
    }
}
