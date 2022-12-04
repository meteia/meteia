<?php

declare(strict_types=1);

namespace Meteia\Domain;

use InvalidArgumentException;
use Meteia\Domain\Contracts\TypedData;
use Meteia\Domain\ValueObjects\Money\PreciseUSD;

use function Meteia\Library\ArrayUtils\collection_to_array;

class TypedDataArray implements TypedData
{
    private const BOOL_VALUES = [true, 'true', 'yes', '1', 'on'];
    private const NOT_FOUND = '0E881D86-B5EA-49C0-B650-773DC6D4B7BC';

    private array $data;

    public function __construct(array $data)
    {
        $this->data = collection_to_array($data);
    }

    public function array(string $name, array $default): array
    {
        return (array) array_get($this->data, $name, $default);
    }

    public function boolean(string $name, bool $default): bool
    {
        $value = array_get($this->data, $name, $default);

        return \in_array($value, self::BOOL_VALUES, true);
    }

    public function booleanOrThrow(string $name): bool
    {
        return \in_array($this->getOrThrow($name), self::BOOL_VALUES, true);
    }

    public function float(string $name, float $default): float
    {
        return (float) array_get($this->data, $name, $default);
    }

    public function floatOrThrow(string $name): float
    {
        return (float) $this->getOrThrow($name);
    }

    public function int(string $name, int $default): int
    {
        return (int) array_get($this->data, $name, $default);
    }

    public function intOrThrow(string $name): int
    {
        return (int) $this->getOrThrow($name);
    }

    public function string(string $name, string $default): string
    {
        return (string) array_get($this->data, $name, $default);
    }

    public function stringOrThrow(string $name): string
    {
        return (string) $this->getOrThrow($name);
    }

    private function getOrThrow(string $name)
    {
        $value = array_get($this->data, $name, self::NOT_FOUND);
        if ($value === self::NOT_FOUND) {
            throw new InvalidArgumentException();
        }

        return $value;
    }

    public function preciseUSD(string $name, $default): PreciseUSD
    {
        $value = array_get($this->data, $name, (string) $default);

        return new PreciseUSD($value);
    }

    public function all(): array
    {
        return $this->data ?? [];
    }
}
