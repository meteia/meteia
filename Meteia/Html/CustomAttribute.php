<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

class CustomAttribute implements Stringable
{
    public function __construct(
        private readonly string $name,
        private readonly string|Stringable|bool $value,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%s="%s"', $this->name, $this->value);
    }
}
