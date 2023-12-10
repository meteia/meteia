<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\ValueObjects\ImmutableArrayValueObject;

readonly class CommandMessages extends ImmutableArrayValueObject
{
    public const string TYPE = CommandMessage::class;
}
