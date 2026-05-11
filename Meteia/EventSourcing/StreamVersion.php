<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use NoDiscard;

final readonly class StreamVersion
{
    public function __construct(
        private int $value,
    ) {
        \assert($value >= 0, 'StreamVersion must be non-negative');
    }

    public static function start(): self
    {
        return new self(0);
    }

    public function asInt(): int
    {
        return $this->value;
    }

    #[NoDiscard]
    public function next(): self
    {
        return new self($this->value + 1);
    }

    public function equalTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
