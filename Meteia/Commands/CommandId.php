<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\ValueObjects\Identity\UniqueId;

readonly class CommandId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'cmd';
    }
}
