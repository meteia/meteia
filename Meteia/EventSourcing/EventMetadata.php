<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\ValueObjects\ImmutableValueObject;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

class EventMetadata extends ImmutableValueObject
{
    public function __construct(public CausationId $causationId, public CorrelationId $correlationId)
    {
    }
}
