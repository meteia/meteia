<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use Meteia\Domain\CommandId;

interface Command
{
    public static function commandTypeId(): CommandId;
}
