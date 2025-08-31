<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Configuration\BooleanValues;
use Psr\Http\Message\ServerRequestInterface;

class TypedRequestQuery
{
    private array $values = [];

    public function __construct(
        private readonly BooleanValues $booleanValues,
        ServerRequestInterface $sri,
    ) {
        parse_str($sri->getUri()->getQuery(), $this->values);
    }

    public function string(string $name, string $default): string
    {
        return $this->values[$name] ?? $default;
    }

    public function int(string $name, int $default): int
    {
        return (int) ($this->values[$name] ?? $default);
    }

    public function boolean(string $name, bool $default): bool
    {
        return $this->booleanValues->boolean($this->values[$name] ?? $default);
    }

    public function float(string $name, float $default): float
    {
        return (float) ($this->values[$name] ?? $default);
    }
}
