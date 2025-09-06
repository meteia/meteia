<?php

declare(strict_types=1);

namespace Meteia\Html;

readonly class CustomElement
{
    /**
     * @param array<string, string|number|boolean> $attributes
     */
    public function __construct(
        public string $name,
        public array $attributes = [],
        public string|\Stringable $children = '',
    ) {}

    public function __toString(): string
    {
        $attrs = array_filter($this->attributes, static fn($val) => !empty($val));
        $attrs = implode(' ', $attrs);

        return sprintf('<%s %s>%s</%s>', $this->name, $attrs, $this->children, $this->name);
    }
}
