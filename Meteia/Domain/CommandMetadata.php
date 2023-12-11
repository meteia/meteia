<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\ValueObjects\AggregateRootId;

class CommandMetadata
{
    public function __construct(public AggregateRootId $aggregateRootId)
    {
    }
}
