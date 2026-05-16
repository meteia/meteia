<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\Contracts\BlankAggregate;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\UnknownAggregate;
use Meteia\ValueObjects\AggregateRootId;

/**
 * @template TAggregateRootId of AggregateRootId
 * @template TAggregate of EventSourced
 */
final readonly class EventSourcedAggregates
{
    /**
     * @param BlankAggregate<TAggregateRootId, TAggregate> $blank
     * @param UnknownAggregate<TAggregateRootId> $missing
     */
    public function __construct(
        private EventStream $events,
        private UnitOfWork $unitOfWork,
        private BlankAggregate $blank,
        private UnknownAggregate $missing,
    ) {}

    /**
     * @param TAggregateRootId $id
     * @return TAggregate
     */
    public function reconstituted(AggregateRootId $id): EventSourced
    {
        $aggregate = $this->blank->of($id);
        $this->events->replay(new StreamId($id->bytes()), $aggregate);

        if (!$aggregate->exists()) {
            throw $this->missing->error($id);
        }

        return $aggregate;
    }

    /**
     * @param TAggregate $aggregate
     */
    public function commit(EventSourced $aggregate): void
    {
        $aggregate->commitInto($this->unitOfWork);
    }
}
