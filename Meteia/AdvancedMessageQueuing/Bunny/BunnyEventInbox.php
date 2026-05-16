<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use DateTimeImmutable;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\Events\EventId;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSink;
use Meteia\Events\PublishedEvent;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use UnexpectedValueException;
use function React\Async\async;

final readonly class BunnyEventInbox implements EventInbox
{
    private BunnyInboxConsumption $consumption;

    private BunnyInboxConsumers $consumers;

    public function __construct(
        private LoggerInterface $log,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
        private AmbientMessageScopeSource $scopeSource,
    ) {
        $this->consumption = new BunnyInboxConsumption();
        $this->consumers = new BunnyInboxConsumers();
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
        $channel->queueBind(exchange: $exchangeName, queue: $queueName);

        $this->consumers->add($queueName, async(function (Message $message, Channel $channel, Client $bunny) use (
            $eventClassName,
            $queueName,
            $sink,
        ): void {
            $eventId = EventId::fromToken($this->header($message, 'message-id'));
            $correlationId = CorrelationId::fromToken($this->header($message, 'correlation-id'));
            $processId = ProcessId::fromToken($this->header($message, 'process-id'));
            $scope = new MessageScope($correlationId, CausationId::fromHex($eventId->hex()), $processId);

            try {
                $event = $this->serializer->deserialize($message->content, $eventClassName, 'json');
                \assert($event instanceof DomainEvent, 'deserialized event must implement DomainEvent');
                $published = PublishedEvent::fromMessage(
                    StreamId::fromToken($this->header($message, 'stream-id')),
                    new StreamVersion((int) $this->header($message, 'stream-version')),
                    $event,
                    CausationId::fromToken($this->header($message, 'causation-id')),
                    $correlationId,
                    new DateTimeImmutable($this->header($message, 'occurred-at')),
                );
                $this->scopeSource->using(
                    $scope,
                    function () use ($sink, $published, $scope, $queueName, $eventId): void {
                        $this->log->info('Received Event', [
                            'queueName' => $queueName,
                            'eventId' => $eventId,
                            'streamId' => $published->streamId(),
                            'streamVersion' => $published->version(),
                        ]);
                        $sink->drain($published, $scope);
                    },
                );
                $channel->ack($message);

                $this->consumption->recordHandledMessage();
                if ($this->consumption->isSatisfied()) {
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
        }));
    }

    #[Override]
    public function run(): void
    {
        $this->consumption->untilShutdown();
        $this->consumers->subscribe($this->loop->channel());
        $this->loop->runUntilShutdown('EventWorkers.Shutdown');
    }

    #[Override]
    public function runOnce(): void
    {
        $this->consumption->oneMessage();
        $this->consumers->subscribe($this->loop->channel());
        $this->loop->runUntilShutdown('EventWorkers.Shutdown');
    }

    private function header(Message $message, string $name): string
    {
        if (!\is_scalar($message->headers[$name] ?? null)) {
            throw new UnexpectedValueException('Event message header must be scalar: ' . $name);
        }

        return (string) $message->headers[$name];
    }
}
