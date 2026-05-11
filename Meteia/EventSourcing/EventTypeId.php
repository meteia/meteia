<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\ValueObjects\Identity\UniqueId;
use Override;

readonly class EventTypeId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'eti';
    }
}
