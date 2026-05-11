<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use Meteia\Commands\Command;
use Meteia\Domain\Contracts\Commands;

class ImmutableCommands extends ImmutableArrayValueObject implements Commands
{
    public const TYPE = Command::class;
}
