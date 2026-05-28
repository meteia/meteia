<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Meteia\ValueObjects\Identity\UniqueId;
use Override;

final readonly class TabId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'tab';
    }
}
