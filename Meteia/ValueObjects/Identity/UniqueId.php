<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Exception;
use JsonSerializable;
use Meteia\ValueObjects\Contracts\HasPrefix;
use Tuupola\Base62;

abstract readonly class UniqueId implements HasPrefix, JsonSerializable
{
    protected const int EPOCH = 1577836800;
    protected const int LEN_ENCODED = 27;
    protected const int LEN_RANDOM = 16;
    protected const int LEN_TIMESTAMP = 4;

    public string $token;

    public function __construct(private string $bytes)
    {
        assert(
            strlen($bytes) === static::LEN_TIMESTAMP + static::LEN_RANDOM,
            'expected ' . static::LEN_TIMESTAMP + static::LEN_RANDOM . ' got ' . strlen($bytes),
        );
        $token = (new Base62())->encode($this->bytes);
        $token = str_pad($token, static::LEN_ENCODED, '0', STR_PAD_LEFT);
        assert(strlen($token) === static::LEN_ENCODED, 'expected ' . static::LEN_ENCODED . ' got ' . strlen($token));
        $this->token = implode('_', [static::prefix(), $token]);
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
        // Discard any additional data on the token (e.g. a selector)
        [$prefix, $token] = explode('_', $token, 3);
        assert($prefix === static::prefix(), 'Expected token with prefix ' . static::prefix());
        $token = ltrim($token, '0');
        $data = (new Base62())->decode($token);

        return new static($data);
    }

    public static function fromHex(string $hex): static
    {
        return new static(hex2bin($hex));
    }

    public function equalTo(UniqueId $other): bool
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

    public function hash(): string
    {
        $hash = $this->binaryHash();

        return implode('_', [static::prefix(), 'hash', (new Base62())->encode($hash)]);
    }

    public function binaryHash(): string
    {
        return hash('sha256', $this->bytes, true);
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
