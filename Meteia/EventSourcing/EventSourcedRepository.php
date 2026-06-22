<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Closure;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\EventSourcing\Contracts\BlankAggregate;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\UnknownAggregate;
use Meteia\ValueObjects\AggregateRootId;
use Throwable;

use function assert;

/**
 * A convenient generic repository for event-sourced aggregates.
 *
 * This removes the need for every bounded context to create a dedicated
 * `EventSourced*` class that also implements `BlankAggregate` and
 * `UnknownAggregate`.
 *
 * Usage example:
 *
 * ```php
 * final readonly class Counters implements Counters
 * {
 *     private EventSourcedRepository $repo;
 *
 *     public function __construct(EventStream $events, UnitOfWork $uow)
 *     {
 *         $this->repo = new EventSourcedRepository(
 *             $events,
 *             $uow,
 *             Counter::blank(...),
 *             fn(CounterId $id) => new UnknownCounter($id),
 *         );
 *     }
 *
 *     public function reconstituted(CounterId $id): Counter
 *     {
 *         return $this->repo->reconstituted($id);
 *     }
 *
 *     public function commit(Counter $counter): void
 *     {
 *         $this->repo->commit($counter);
 *     }
 * }
 * ```
 *
 * @template TId of AggregateRootId
 * @template TAggregate of EventSourced
 */
final readonly class EventSourcedRepository
{
    private EventSourcedAggregates $aggregates;

    /**
     * @param Closure(TId): TAggregate $blank
     * @param Closure(TId): Throwable  $unknown
     */
    public function __construct(
        EventStream $events,
        UnitOfWork $unitOfWork,
        Closure $blank,
        Closure $unknown,
    ) {
        $this->aggregates = new EventSourcedAggregates(
            $events,
            $unitOfWork,
            new BlankRole($blank),
            new UnknownRole($unknown),
        );
    }

    /**
     * @param TId $id
     * @return TAggregate
     */
    /**
     * @mago-expect analyze:invalid-return-statement -- The analyzer does not yet fully propagate the generic through EventSourcedAggregates; the runtime contract is correct.
     */
    public function reconstituted(AggregateRootId $id): EventSourced
    {
        $aggregate = $this->aggregates->reconstituted($id);

        assert($aggregate instanceof EventSourced, 'EventSourcedAggregates must return an instance of EventSourced');

        return $aggregate;
    }

    /**
     * Reconstitute the aggregate, or return a blank one when it does not exist yet, for aggregates
     * whose first domain event also creates them. See {@see EventSourcedAggregates::draft()}.
     *
     * @param TId $id
     * @return TAggregate
     */
    /**
     * @mago-expect analyze:invalid-return-statement -- The analyzer does not yet fully propagate the generic through EventSourcedAggregates; the runtime contract is correct.
     */
    public function draft(AggregateRootId $id): EventSourced
    {
        $aggregate = $this->aggregates->draft($id);

        assert($aggregate instanceof EventSourced, 'EventSourcedAggregates must return an instance of EventSourced');

        return $aggregate;
    }

    /**
     * @param TAggregate $aggregate
     */
    public function commit(EventSourced $aggregate): void
    {
        $this->aggregates->commit($aggregate);
    }
}
