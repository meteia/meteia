<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Closure;
use Meteia\EventSourcing\Contracts\UnknownAggregate;
use Meteia\ValueObjects\AggregateRootId;
use Override;
use Throwable;

/**
 * @internal
 *
 * @template TId of AggregateRootId
 * @implements UnknownAggregate<TId>
 */
final readonly class UnknownRole implements UnknownAggregate
{
    /**
     * @param Closure(TId): Throwable $unknown
     */
    public function __construct(
        private Closure $unknown,
    ) {}

    #[Override]
    public function error(AggregateRootId $id): Throwable
    {
        return ($this->unknown)($id);
    }
}
