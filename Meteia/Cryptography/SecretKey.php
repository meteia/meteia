<?php

declare(strict_types=1);

namespace Meteia\Cryptography;

use Meteia\ValueObjects\Identity\UniqueId;

class SecretKey extends UniqueId
{
    protected const LEN_ENCODED = 43;
    protected const LEN_RANDOM = 32;
    protected const LEN_TIMESTAMP = 0;

    public static function prefix(): string
    {
        return 'sk';
    }
}
