<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\StackTraces;

class Line
{
    public function __construct(
        public readonly string $text,
        public readonly int $number,
        public readonly bool $shouldHighlight,
    ) {}
}
