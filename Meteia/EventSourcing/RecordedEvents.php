<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\ValueObjects\ImmutableArrayValueObject;

final readonly class RecordedEvents extends ImmutableArrayValueObject
{
    public const string TYPE = RecordedEvent::class;
}
