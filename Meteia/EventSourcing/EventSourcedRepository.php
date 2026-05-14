<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\ValueObjects\AggregateRootId;
use Throwable;

/**
 * @template TId of AggregateRootId
 * @template TAgg of EventSourced
 */
abstract class EventSourcedRepository
{
    public function __construct(
        private readonly EventStream $events,
        private readonly UnitOfWork $unitOfWork,
    ) {}

    /**
     * @param TId $id
     * @return TAgg
     */
    public function load(AggregateRootId $id): EventSourced
    {
        $aggregate = $this->blank($id);
        $this->events->replay(new StreamId($id->bytes()), $aggregate);
        if (!$aggregate->exists()) {
            throw $this->unknown($id);
        }

        return $aggregate;
    }

    /**
     * @param TAgg $aggregate
     */
    public function append(EventSourced $aggregate): void
    {
        $aggregate->commitInto($this->unitOfWork);
    }

    /**
     * @param TId $id
     * @return TAgg
     */
    abstract protected function blank(AggregateRootId $id): EventSourced;

    /**
     * @param TId $id
     */
    abstract protected function unknown(AggregateRootId $id): Throwable;
}
