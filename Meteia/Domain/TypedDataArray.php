<?php

declare(strict_types=1);

namespace Meteia\Domain;

use InvalidArgumentException;
use Meteia\Domain\Contracts\TypedData;
use Meteia\ValueObjects\Money\PreciseUsd;
use Override;
use Stringable;
use Traversable;

class TypedDataArray implements TypedData
{
    private const BOOL_VALUES = [true, 'true', 'yes', '1', 'on'];
    private const NOT_FOUND = '0E881D86-B5EA-49C0-B650-773DC6D4B7BC';

    /** @var array<array-key, mixed> */
    private array $data;

    /**
     * @param array<array-key, mixed>|Traversable<array-key, mixed> $data
     */
    public function __construct(array|Traversable $data)
    {
        $this->data = $data instanceof Traversable ? iterator_to_array($data) : $data;
    }

    #[Override]
    public function array(string $name, array $default): array
    {
        $value = self::dotGet($this->data, $name, $default);

        return \is_array($value) ? $value : $default;
    }

    #[Override]
    public function boolean(string $name, bool $default): bool
    {
        $value = self::dotGet($this->data, $name, $default);

        return \in_array($value, self::BOOL_VALUES, true);
    }

    #[Override]
    public function booleanOrThrow(string $name): bool
    {
        return \in_array($this->getOrThrow($name), self::BOOL_VALUES, true);
    }

    #[Override]
    public function float(string $name, float $default): float
    {
        $value = self::dotGet($this->data, $name, $default);

        return \is_scalar($value) ? (float) $value : $default;
    }

    #[Override]
    public function floatOrThrow(string $name): float
    {
        $value = $this->getOrThrow($name);
        \assert(\is_scalar($value));

        return (float) $value;
    }

    #[Override]
    public function int(string $name, int $default): int
    {
        $value = self::dotGet($this->data, $name, $default);

        return \is_scalar($value) ? (int) $value : $default;
    }

    #[Override]
    public function intOrThrow(string $name): int
    {
        $value = $this->getOrThrow($name);
        \assert(\is_scalar($value));

        return (int) $value;
    }

    #[Override]
    public function string(string $name, string $default): string
    {
        $value = self::dotGet($this->data, $name, $default);

        return \is_scalar($value) || $value instanceof Stringable ? (string) $value : $default;
    }

    #[Override]
    public function stringOrThrow(string $name): string
    {
        $value = $this->getOrThrow($name);
        \assert(\is_scalar($value) || $value instanceof Stringable);

        return (string) $value;
    }

    public function preciseUSD(string $name, string|float|int|Stringable $default): PreciseUsd
    {
        $value = self::dotGet($this->data, $name, (string) $default);
        \assert(\is_string($value) || \is_float($value) || \is_int($value) || $value instanceof Stringable);

        return new PreciseUsd($value);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    private function getOrThrow(string $name): mixed
    {
        $value = self::dotGet($this->data, $name, self::NOT_FOUND);
        if ($value === self::NOT_FOUND) {
            throw new InvalidArgumentException();
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private static function dotGet(array $data, string $key, mixed $default): mixed
    {
        if (\array_key_exists($key, $data)) {
            return $data[$key];
        }
        $current = $data;
        foreach (explode('.', $key) as $segment) {
            if (!\is_array($current) || !\array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
