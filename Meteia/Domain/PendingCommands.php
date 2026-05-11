<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\ValueObjects\ImmutableArrayValueObject;

final readonly class PendingCommands extends ImmutableArrayValueObject
{
    public const string TYPE = PendingCommand::class;
}
