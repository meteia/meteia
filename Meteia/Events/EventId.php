<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\ValueObjects\Identity\UniqueId;

readonly class EventId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'evt';
    }
}
