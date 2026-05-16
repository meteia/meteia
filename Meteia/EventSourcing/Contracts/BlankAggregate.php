<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\ValueObjects\AggregateRootId;

/**
 * @template TAggregateRootId of AggregateRootId
 * @template TAggregate of EventSourced
 */
interface BlankAggregate
{
    /**
     * @param TAggregateRootId $id
     * @return TAggregate
     */
    public function of(AggregateRootId $id): EventSourced;
}
