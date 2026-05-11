<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\ValueObjects\Identity\UniqueId;
use Override;

final readonly class StreamId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'str';
    }
}
