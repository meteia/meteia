<?php

declare(strict_types=1);

namespace Meteia\Logging;

use Meteia\ValueObjects\Identity\UniqueId;

readonly class LogSourceId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'log';
    }
}
