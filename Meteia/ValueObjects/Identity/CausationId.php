<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

readonly class CausationId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'cus';
    }
}
