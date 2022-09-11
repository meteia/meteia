<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Exception;
use Tuupola\Base62;

abstract class UniqueId implements \Stringable, \JsonSerializable
{
    protected const EPOCH = 1577836800;
    protected const LEN_ENCODED = 27;
    protected const LEN_RANDOM = 16;
    protected const LEN_TIMESTAMP = 4;

    public readonly string $token;

    public function __construct(readonly string $bytes)
    {
        assert(
            strlen($bytes) === static::LEN_TIMESTAMP + static::LEN_RANDOM,
            'expected ' . static::LEN_TIMESTAMP + static::LEN_RANDOM . ' got ' . strlen($bytes),
        );
        $token = (new Base62())->encode($this->bytes);
        $padding = static::LEN_ENCODED - strlen($token);
        if ($padding > 0) {
            $token = str_repeat('0', $padding) . $token;
        }
        assert(strlen($token) === static::LEN_ENCODED, 'expected ' . static::LEN_ENCODED . ' got ' . strlen($token));
        $this->token = $token;
    }

    public static function random(): static
    {
        $data = static::LEN_TIMESTAMP ? pack('N', time() - static::EPOCH) : '';
        // $data = pack("N", random_int(0, PHP_INT_MAX));
        $data .= random_bytes(static::LEN_RANDOM);

        return new static($data);
    }

    public static function fromToken(string $token): static
    {
        $token = ltrim($token, '0');
        $data = (new Base62())->decode($token);

        return new static($data);
    }

    public static function fromHex(string $hex): static
    {
        return new static(hex2bin($hex));
    }

    public function equalTo(UniqueId $other)
    {
        return hash_equals($this->bytes, $other->bytes);
    }

    public function randomBytes(int $len): string
    {
        if (static::LEN_RANDOM < $len) {
            throw new Exception('Insufficient random data in underlying data');
        }

        return substr($this->bytes, -$len);
    }

    public function hex(): string
    {
        return bin2hex($this->bytes);
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public function jsonSerialize(): string
    {
        return $this->token;
    }
}
