<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Override;

readonly class CausationId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'cus';
    }
}
