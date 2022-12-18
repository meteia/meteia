<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

class ClassAttribute implements Stringable
{
    public function __construct(private readonly string|Stringable $className)
    {
    }

    public function __toString(): string
    {
        return sprintf('class="%s"', $this->className);
    }
}
