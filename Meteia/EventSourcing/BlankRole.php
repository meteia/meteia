<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Closure;
use Meteia\EventSourcing\Contracts\BlankAggregate;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\ValueObjects\AggregateRootId;
use Override;

/**
 * @internal
 *
 * @template TId of AggregateRootId
 * @template TAggregate of EventSourced
 * @implements BlankAggregate<TId, TAggregate>
 */
final readonly class BlankRole implements BlankAggregate
{
    /**
     * @param Closure(TId): TAggregate $blank
     */
    public function __construct(
        private Closure $blank,
    ) {}

    #[Override]
    public function of(AggregateRootId $id): EventSourced
    {
        return ($this->blank)($id);
    }
}
