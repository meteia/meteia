<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\Events\Event;
use Meteia\Events\EventId;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final readonly class BunnyEventInbox implements EventInbox
{
    private stdClass $runState;

    public function __construct(
        private LoggerInterface $log,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
        private AmbientMessageScopeSource $scopeSource,
    ) {
        $this->runState = (object) ['maxMessages' => 0, 'processed' => 0];
    }

    #[Override]
    public function subscribe(string $eventClassName, string $sinkClassName, EventSink $sink): void
    {
        $channel = $this->loop->channel();
        $exchangeName = str_replace('\\', '.', $eventClassName);
        $queueName = str_replace('\\', '.', $sinkClassName);
        $this->log->info('Subscribing Event Sink', [
            'event' => $eventClassName,
            'exchange' => $exchangeName,
            'queue' => $queueName,
        ]);

        $channel->exchangeDeclare($exchangeName, exchangeType: 'fanout', durable: true);
        $channel->queueDeclare($queueName, durable: true);
        $channel->queueBind($queueName, $exchangeName);

        $channel->consume(function (Message $message, Channel $channel, Client $bunny) use (
            $eventClassName,
            $queueName,
            $sink,
        ): void {
            $eventId = EventId::fromToken((string) $message->headers['message-id']);
            $correlationId = CorrelationId::fromToken((string) $message->headers['correlation-id']);
            $processId = ProcessId::fromToken((string) $message->headers['process-id']);
            $scope = new MessageScope($correlationId, CausationId::fromHex($eventId->hex()), $processId);

            try {
                $event = $this->serializer->deserialize($message->content, $eventClassName, 'json');
                \assert($event instanceof Event, 'deserialized event must implement Event');
                $this->scopeSource->using($scope, function () use ($sink, $event, $scope, $queueName, $eventId): void {
                    $this->log->info('Received Event', [
                        'queueName' => $queueName,
                        'eventId' => $eventId,
                    ]);
                    $sink->drain($event, $scope);
                });
                $channel->ack($message);

                $this->runState->processed++;
                if ($this->runState->maxMessages > 0 && $this->runState->processed >= $this->runState->maxMessages) {
                    $this->log->info('Once mode: disconnecting after processing one message', [
                        'queueName' => $queueName,
                    ]);
                    $bunny->disconnect();
                    exit(0);
                }
            } catch (Throwable $t) {
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), ['queueName' => $queueName]);
            }
        }, $queueName);
    }

    #[Override]
    public function run(): void
    {
        $this->runState->maxMessages = 0;
        $this->runState->processed = 0;
        $this->loop->runUntilShutdown('EventWorkers.Shutdown');
    }

    #[Override]
    public function runOnce(): void
    {
        $this->runState->maxMessages = 1;
        $this->runState->processed = 0;
        $this->loop->runUntilShutdown('EventWorkers.Shutdown');
    }
}
