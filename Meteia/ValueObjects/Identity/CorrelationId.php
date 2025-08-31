<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

readonly class CorrelationId extends UniqueId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'crr';
    }
}
