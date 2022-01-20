<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\ValueObjects\ImmutableArrayValueObject;

class EventParentVersions extends ImmutableArrayValueObject
{
    public const TYPE = EventVersion::class;
}
