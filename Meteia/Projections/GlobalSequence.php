<?php

declare(strict_types=1);

namespace Meteia\Projections;

final readonly class GlobalSequence
{
    public function __construct(
        private int $value,
    ) {
        \assert($value >= 0, 'GlobalSequence must be non-negative');
    }

    public static function start(): self
    {
        return new self(0);
    }

    public function asInt(): int
    {
        return $this->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function equalTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
