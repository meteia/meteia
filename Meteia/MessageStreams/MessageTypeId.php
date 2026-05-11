<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Meteia\ValueObjects\Identity\UniqueId;
use Override;

final readonly class MessageTypeId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'mti';
    }
}
