<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\ValueObjects\Identity\MessageScope;

/**
 * Records that certain domain events must be delivered to the message bus.
 *
 * Implementations are expected to be called inside the same database
 * transaction that wrote the original domain_events rows (see
 * afterSuccessfulPersist in the UnitOfWork).
 */
interface EventOutbox
{
    /**
     * @param array<string, array{StreamId, list<RecordedEvent>}> $eventGroups
     */
    public function record(MessageScope $scope, array $eventGroups): void;
}
