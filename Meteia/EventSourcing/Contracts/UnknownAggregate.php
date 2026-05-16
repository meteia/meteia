<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\ValueObjects\AggregateRootId;
use Throwable;

/**
 * @template TAggregateRootId of AggregateRootId
 */
interface UnknownAggregate
{
    /**
     * @param TAggregateRootId $id
     */
    public function error(AggregateRootId $id): Throwable;
}
