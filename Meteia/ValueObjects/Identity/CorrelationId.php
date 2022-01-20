<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

class CorrelationId extends UniqueId
{
    public static function prefix(): string
    {
        return 'cor';
    }
}
