<?php

declare(strict_types=1);

namespace Meteia\Events;

use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\EventTypeId;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

// @mago-expect lint:too-many-methods -- published events intentionally expose the recorded fact plus durable message identity for idempotent sinks
final readonly class PublishedEvent
{
    public function __construct(
        private RecordedEvent $recorded,
    ) {}

    public static function fromRecorded(RecordedEvent $event): self
    {
        return new self($event);
    }

    public static function fromMessage(
        StreamId $streamId,
        StreamVersion $version,
        DomainEvent $event,
        CausationId $causationId,
        CorrelationId $correlationId,
        DateTimeImmutable $occurredAt,
    ): self {
        return new self(new RecordedEvent(
            new PendingEvent($streamId, $version, $event),
            $causationId,
            $correlationId,
            $occurredAt,
        ));
    }

    public function recorded(): RecordedEvent
    {
        return $this->recorded;
    }

    public function streamId(): StreamId
    {
        return $this->recorded->streamId();
    }

    public function version(): StreamVersion
    {
        return $this->recorded->version();
    }

    public function fact(): DomainEvent
    {
        return $this->recorded->event();
    }

    public function eventTypeId(): EventTypeId
    {
        return $this->recorded->eventTypeId();
    }

    public function causedBy(): CausationId
    {
        return $this->recorded->causedBy();
    }

    public function correlatedTo(): CorrelationId
    {
        return $this->recorded->correlatedTo();
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->recorded->occurredAt();
    }

    public function messageId(): EventId
    {
        $bytes = substr(hash('sha256', $this->streamId()->bytes() . ':' . $this->version()->asInt(), true), 0, 20);

        return new EventId($bytes);
    }
}
