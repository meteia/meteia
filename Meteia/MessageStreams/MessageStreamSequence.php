<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

final readonly class MessageStreamSequence
{
    public function __construct(
        private int $value,
    ) {
        \assert($value >= 0, 'MessageStreamSequence must be non-negative');
    }

    public function asInt(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
