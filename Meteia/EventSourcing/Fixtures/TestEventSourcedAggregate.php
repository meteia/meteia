<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\UniqueId;
use Override;

/**
 * @internal
 */
final class TestEventSourcedAggregate implements EventSourced
{
    private bool $exists = false;

    private int $observedSequence = -1;

    public function __construct(
        private readonly AggregateRootId $id,
    ) {}

    #[Override]
    public function commitInto(UnitOfWorkContext $unitOfWorkContext): void
    {
        $unitOfWorkContext->caused(new PendingEvents([
            new PendingEvent(new StreamId($this->id->bytes()), new StreamVersion(0), new AggregateRecorded()),
        ]));
    }

    #[Override]
    public function handleEventMessage(UniqueId $streamId, DomainEvent $event, int $eventSequence): void
    {
        $this->exists = true;
        $this->observedSequence = $eventSequence;
    }

    #[Override]
    public function exists(): bool
    {
        return $this->exists;
    }

    public function observedSequence(): int
    {
        return $this->observedSequence;
    }
}
