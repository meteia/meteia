<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class PendingEvent
{
    public function __construct(
        private StreamId $streamId,
        private StreamVersion $version,
        private DomainEvent $event,
    ) {}

    public function streamId(): StreamId
    {
        return $this->streamId;
    }

    public function version(): StreamVersion
    {
        return $this->version;
    }

    public function event(): DomainEvent
    {
        return $this->event;
    }

    public function recordedWith(
        CausationId $causationId,
        CorrelationId $correlationId,
        DateTimeImmutable $occurredAt,
    ): RecordedEvent {
        return new RecordedEvent($this, $causationId, $correlationId, $occurredAt);
    }
}
