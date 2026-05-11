<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\Events;

readonly class ImmutableEvents extends ImmutableArrayValueObject implements Events
{
    public const TYPE = DomainEvent::class;
}
