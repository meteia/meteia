<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

class CausationId extends UniqueId
{
    public static function prefix(): string
    {
        return 'cau';
    }
}
