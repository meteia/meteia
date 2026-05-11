<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\Contracts\EventMessageTarget;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class RecordedEvent
{
    public function __construct(
        private PendingEvent $pending,
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function streamId(): StreamId
    {
        return $this->pending->streamId();
    }

    public function version(): StreamVersion
    {
        return $this->pending->version();
    }

    public function event(): DomainEvent
    {
        return $this->pending->event();
    }

    public function eventTypeId(): EventTypeId
    {
        return $this->pending->event()::eventTypeId();
    }

    public function causedBy(): CausationId
    {
        return $this->causationId;
    }

    public function correlatedTo(): CorrelationId
    {
        return $this->correlationId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function applyTo(EventMessageTarget $target): void
    {
        $target->handleEventMessage($this->streamId(), $this->event(), $this->version()->asInt());
    }
}
