<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\ValueObjects\Identity\UniqueId;

class EventTypeId extends UniqueId
{
    public static function prefix(): string
    {
        return 'eti';
    }
}
