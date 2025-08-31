<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Bunny\Protocol\MethodQueueDeclareOkFrame;
use Meteia\Events\Event;
use Meteia\Events\EventBus;
use Meteia\Events\EventId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

readonly class BunnyEventBus implements EventBus
{
    public function __construct(
        private Client $client,
        private Channel $channel,
        private LoggerInterface $log,
        private Serializer $serializer,
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private ProcessId $processId,
    ) {}

    #[\Override]
    public function publishEvent(Event $event): void
    {
        $payload = $this->serializer->serialize($event, 'json');
        $exchangeName = $this->exchangeNameForEvent($event::class);
        $this->channel->publish(
            $payload,
            [
                'message-id' => (string) EventId::random(),
                'content-type' => 'application/json',
                'correlation-id' => (string) $this->correlationId,
                'causation-id' => (string) $this->causationId,
                'process-id' => (string) $this->processId,
            ],
            $exchangeName,
        );
        $this->log->info('Published Event', [
            'event' => $event::class,
            'exchange' => $exchangeName,
        ]);
    }

    #[\Override]
    public function registerEventHandler(
        string $eventClassName,
        string $eventHandlerClassName,
        callable $eventHandler,
    ): void {
        $exchangeName = $this->exchangeNameForEvent($eventClassName);
        $queueName = $this->queueNameForEventHandler($eventHandlerClassName);
        $this->log->info('Registering Event Handler', [
            'event' => $eventClassName,
            'exchange' => $exchangeName,
            'queue' => $queueName,
        ]);

        $this->channel->exchangeDeclare($exchangeName, exchangeType: 'fanout', durable: true);
        $this->log->info('Declared Exchange', [
            'exchange' => $exchangeName,
        ]);

        $this->channel->queueDeclare($queueName, durable: true);
        $this->log->info('Declared Queue', [
            'queue' => $queueName,
        ]);

        $this->channel->queueBind($queueName, $exchangeName);
        $this->log->info('Bound Queue to Exchange', [
            'queue' => $queueName,
            'exchange' => $exchangeName,
        ]);

        $this->channel->consume(
            function (Message $message, Channel $channel, Client $bunny) use (
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
                    $this->log->error($t->getMessage(), [
                        'queueName' => $queueName,
                    ]);
                }
            },
            $queueName,
        );
    }

    #[\Override]
    public function run(): void
    {
        $shutdownExchangeName = 'EventWorkers.Shutdown';
        $this->channel->exchangeDeclare($shutdownExchangeName, exchangeType: 'fanout', durable: true);
        $this->log->info('Declared Exchange', [
            'exchange' => $shutdownExchangeName,
        ]);

        /** @var MethodQueueDeclareOkFrame $result */
        $result = $this->channel->queueDeclare(exclusive: true);
        $this->log->info('Declared Queue', [
            'queue' => $result->queue,
        ]);
        $this->channel->queueBind($result->queue, $shutdownExchangeName);
        $this->channel->consume(function (Message $message, Channel $channel, Client $bunny): void {
            $this->log->info('Shutdown Message Received');
            $channel->ack($message);
            $bunny->disconnect();
            sleep(random_int(1, 5));

            exit(0);
        }, $result->queue);

        $this->client->run();
    }

    private function queueNameForEventHandler(string $eventhandlerClassName): string
    {
        return str_replace('\\', '.', $eventhandlerClassName);
    }

    private function exchangeNameForEvent(string $eventClassName): string
    {
        return str_replace('\\', '.', $eventClassName);
    }
}
