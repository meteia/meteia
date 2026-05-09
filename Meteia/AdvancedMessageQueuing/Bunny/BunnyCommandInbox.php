<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandSink;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyCommandInbox implements CommandInbox
{
    public function __construct(
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
        private AmbientMessageScopeSource $scopeSource,
    ) {}

    #[\Override]
    public function subscribe(string $commandClassName, CommandSink $sink): void
    {
        $channel = $this->loop->channel();
        $channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $queueName = str_replace('\\', '.', $commandClassName);
        $this->log->info('Subscribing Command Sink', ['queue' => $queueName]);

        $channel->queueDeclare(queue: $queueName, durable: true);
        $channel->queueBind(exchange: (string) $this->exchangeName, queue: $queueName, routingKey: $queueName);

        $channel->consume(function (Message $message, Channel $channel, Client $bunny) use (
            $commandClassName,
            $queueName,
            $sink,
        ): void {
            $commandId = CommandId::fromToken($message->headers['message-id']);
            $correlationId = CorrelationId::fromToken($message->headers['correlation-id']);
            $processId = ProcessId::fromToken($message->headers['process-id']);
            $scope = new MessageScope($correlationId, CausationId::fromHex($commandId->hex()), $processId);

            try {
                $command = $this->serializer->deserialize($message->content, $commandClassName, 'json');
                $this->scopeSource->using($scope, function () use (
                    $sink,
                    $command,
                    $scope,
                    $queueName,
                    $commandId,
                ): void {
                    $this->log->info('Received Command', [
                        'queueName' => $queueName,
                        'commandId' => $commandId,
                    ]);
                    $sink->drain($command, $scope);
                });
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
