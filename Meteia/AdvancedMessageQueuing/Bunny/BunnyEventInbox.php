<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\Events\EventId;
use Meteia\Events\EventInbox;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyEventInbox implements EventInbox
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
    ) {}

    #[\Override]
    public function subscribe(string $eventClassName, string $eventHandlerClassName, callable $eventHandler): void
    {
        $exchangeName = str_replace('\\', '.', $eventClassName);
        $queueName = str_replace('\\', '.', $eventHandlerClassName);
        $this->log->info('Subscribing Event Handler', [
            'event' => $eventClassName,
            'exchange' => $exchangeName,
            'queue' => $queueName,
        ]);

        $this->channel->exchangeDeclare($exchangeName, exchangeType: 'fanout', durable: true);
        $this->channel->queueDeclare($queueName, durable: true);
        $this->channel->queueBind($queueName, $exchangeName);

        $this->channel->consume(function (Message $message, Channel $channel, Client $bunny) use (
            $eventClassName,
            $queueName,
            $eventHandler,
        ): void {
            $eventId = EventId::fromToken($message->headers['message-id']);
            $correlationId = CorrelationId::fromToken($message->headers['correlation-id']);
            $causationId = CausationId::fromToken($message->headers['causation-id']);
            $processId = ProcessId::fromToken($message->headers['process-id']);
            $this->log->info('Received Event', [
                'queueName' => $queueName,
                'eventId' => $eventId,
                'correlationId' => $correlationId,
                'causationId' => $causationId,
                'processId' => $processId,
            ]);

            try {
                $event = $this->serializer->deserialize($message->content, $eventClassName, 'json');
                $eventHandler($event, $eventId, $correlationId, $causationId, $processId);
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
        $this->loop->runUntilShutdown('EventWorkers.Shutdown');
    }
}
