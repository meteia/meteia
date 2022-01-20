<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\ValueObjects\ImmutableArrayValueObject;

class CommandMessages extends ImmutableArrayValueObject
{
    public const TYPE = CommandMessage::class;
}
