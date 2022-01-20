<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\ValueObjects\AggregateRootId;

interface EventMessageTarget
{
    public function handleEventMessage(
        AggregateRootId $aggregateRootId,
        DomainEvent $event,
        int $eventSequence,
    );
}
