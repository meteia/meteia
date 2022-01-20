<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\ValueObjects\ImmutableArrayValueObject;

class EventMessages extends ImmutableArrayValueObject
{
    public const TYPE = EventMessage::class;
}
