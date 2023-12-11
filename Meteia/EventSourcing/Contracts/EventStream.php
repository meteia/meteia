<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\EventSourcing\EventTypeId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

interface EventStream
{
    public function replay(AggregateRootId $aggregateRootId, EventSourced $target): EventSourced;

    public function append(
        AggregateRootId $aggregateRootId,
        int $aggregateSequence,
        EventTypeId $eventTypeId,
        DomainEvent $event,
        CausationId $causationId,
        CorrelationId $correlationId,
    ): void;
}
