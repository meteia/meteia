<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\ValueObjects\ImmutableArrayValueObject;

final readonly class PendingEvents extends ImmutableArrayValueObject
{
    public const string TYPE = PendingEvent::class;
}
