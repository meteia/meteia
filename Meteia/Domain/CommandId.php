<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\ValueObjects\Identity\UniqueId;

class CommandId extends UniqueId
{
    public static function prefix(): string
    {
        return 'cmd';
    }
}
