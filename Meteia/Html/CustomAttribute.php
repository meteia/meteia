<?php

declare(strict_types=1);

namespace Meteia\Html;

class CustomAttribute implements \Stringable
{
    public function __construct(private readonly string $name, private readonly bool|string|\Stringable $value)
    {
    }

    public function __toString(): string
    {
        return sprintf('%s="%s"', $this->name, $this->value);
    }
}
