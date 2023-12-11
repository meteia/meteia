<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\Command;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandMessageHandler;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

readonly class BunnyCommandBus implements CommandBus
{
    public function __construct(
        private Client $client,
        private Channel $channel,
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private Serializer $serializer,
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private ProcessId $processId,
    ) {
        $this->channel->exchangeDeclare((string) $this->exchangeName, durable: true);
    }

    #[\Override]
    public function publishCommand(Command $command): void
    {
        $payload = $this->serializer->serialize($command, 'json');
        $this->channel->publish(
            $payload,
            [
                'message-id' => (string) CommandId::random(),
                'content-type' => 'application/json',
                'correlation-id' => (string) $this->correlationId,
                'causation-id' => (string) $this->causationId,
                'process-id' => (string) $this->processId,
            ],
            $this->exchangeName,
            $this->queueNameForCommand($command::class),
        );
        $this->log->info('Published Command', [
            'command' => $command::class,
            'exchange' => $this->exchangeName,
            'queue' => $this->queueNameForCommand($command::class),
        ]);
    }

    #[\Override]
    public function registerCommandHandler(string $commandClassName, CommandMessageHandler $handler): void
    {
        $queueName = $this->queueNameForCommand($commandClassName);
        $this->log->info('Registering Command Handler', ['queue' => $queueName]);

        $ok = $this->channel->queueDeclare(queue: $queueName, durable: true);
        $this->log->info('Command Queue Declared', ['queue' => $queueName, 'status' => $ok ? 'ok' : 'failed']);

        $ok = $this->channel->queueBind(queue: $queueName, exchange: $this->exchangeName, routingKey: $queueName);
        $this->log->info('Command Queue Bound', [
            'queue' => $queueName,
            'exchange' => $this->exchangeName,
            'status' => $ok ? 'ok' : 'failed',
        ]);

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
            } catch (\Throwable $t) {
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), [
                    'queueName' => $queueName,
                ]);
            }
            $channel->ack($message);
        }, $queueName);
    }

    #[\Override]
    public function run(): void
    {
        $this->client->run();
    }

    private function queueNameForCommand(string $commandClassname): string
    {
        return str_replace('\\', '.', $commandClassname);
    }
}
