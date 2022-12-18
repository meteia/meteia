<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

class CustomElement
{
    public function __construct(
        public readonly string $name,
        public readonly array $attributes = [],
        public readonly string|Stringable $children = '',
    ) {
    }

    public function __toString(): string
    {
        $attrs = array_filter($this->attributes, fn ($val) => !empty($val));
        $attrs = implode(' ', $attrs);

        return "<{$this->name} {$attrs}>{$this->children}</{$this->name}>";
    }
}
