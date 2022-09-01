<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Exception;
use Meteia\ValueObjects\Contracts\HasPrefix;
use Tuupola\Base62;

abstract class UniqueId implements HasPrefix, \Stringable
{
    protected const EPOCH = 1577836800;
    protected const LEN_ENCODED = 27;
    protected const LEN_RANDOM = 16;
    protected const LEN_TIMESTAMP = 4;
    protected const PREFIX = '!!!';

    protected string $token;

    public function __construct(public readonly string $bytes)
    {
        assert(strlen($bytes) === static::LEN_TIMESTAMP + static::LEN_RANDOM);
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
        [$prefix, $token] = explode('_', $token, 2);
        assert($prefix === static::prefix(), 'Expected token with prefix ' . static::prefix());
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

    public function token(): string
    {
        if (!isset($this->token)) {
            $token = (new Base62())->encode($this->bytes);
            if ($padding = static::LEN_ENCODED - strlen($token)) {
                $token = str_repeat('0', $padding) . $token;
            }
            assert(strlen($token) === static::LEN_ENCODED, 'expected ' . static::LEN_ENCODED . ' got ' . strlen($token));
            $this->token = implode('_', [static::prefix(), $token]);
        }

        return $this->token;
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
        return $this->token();
    }

    public function jsonSerialize()
    {
        return $this->token();
    }

    /**
     * FIXME: This feels super hacky...
     */
    public function __sleep(): array
    {
        $this->token();

        return ['token'];
    }

    /**
     * FIXME: This feels super hacky...
     */
    public function __wakeup(): void
    {
        $this->bytes = (static::fromToken($this->token)->bytes);
    }
}
