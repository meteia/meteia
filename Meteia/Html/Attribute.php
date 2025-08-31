<?php

declare(strict_types=1);

namespace Meteia\Html;

class Attribute
{
    public function __construct(
        public string $name,
        public bool|string|\Stringable $value,
    ) {}
}
