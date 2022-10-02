<?php

declare(strict_types=1);

namespace Meteia\Cryptography;

use Meteia\ValueObjects\Primitive\StringLiteral;
use Tuupola\Base62;

class Hash extends StringLiteral
{
    private static Base62 $base62;

    public function __construct(string $hexHash)
    {
        parent::__construct($hexHash);
    }

    public static function fromBase62(string $base62): static
    {
        if (!isset(static::$base62)) {
            static::$base62 = new Base62();
        }

        return new static(bin2hex(static::$base62->decode($base62)));
    }

    public static function fromBinary(string $hexBinary): static
    {
        return new static(bin2hex($hexBinary));
    }

    public function base62(): string
    {
        if (!isset(static::$base62)) {
            static::$base62 = new Base62();
        }

        return static::$base62->encode($this->binary());
    }

    public function binary(): string
    {
        return hex2bin($this->value);
    }

    public function hex(): string
    {
        return $this->value;
    }
}
