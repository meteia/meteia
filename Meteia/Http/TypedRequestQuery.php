<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Configuration\BooleanValues;
use Psr\Http\Message\ServerRequestInterface;

class TypedRequestQuery
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function __construct(
        private readonly BooleanValues $booleanValues,
        ServerRequestInterface $sri,
    ) {
        parse_str($sri->getUri()->getQuery(), $this->values);
    }

    public function string(string $name, string $default): string
    {
        $value = $this->values[$name] ?? $default;

        return is_string($value) ? $value : $default;
    }

    public function int(string $name, int $default): int
    {
        $value = $this->values[$name] ?? $default;
        if (!is_scalar($value)) {
            return $default;
        }

        return (int) $value;
    }

    public function boolean(string $name, bool $default): bool
    {
        $value = $this->values[$name] ?? $default;
        if (!is_string($value) && !is_int($value) && !is_bool($value)) {
            return $default;
        }

        return $this->booleanValues->boolean($value);
    }

    public function float(string $name, float $default): float
    {
        $value = $this->values[$name] ?? $default;
        if (!is_scalar($value)) {
            return $default;
        }

        return (float) $value;
    }
}
