<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Cryptography\Hash;

class FileHash extends Hash
{
    public static function fromHash(Hash $hash): self
    {
        return new self($hash->value);
    }
}
