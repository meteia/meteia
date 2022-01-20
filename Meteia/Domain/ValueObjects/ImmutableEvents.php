<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\Events;

class ImmutableEvents extends ImmutableArrayValueObject implements Events
{
    public const TYPE = DomainEvent::class;
}
