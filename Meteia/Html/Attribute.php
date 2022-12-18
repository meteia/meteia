<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

class Attribute
{
    public function __construct(
        public string $name,
        public string|Stringable|bool $value,
    ) {
    }
}
