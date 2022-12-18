<?php

declare(strict_types=1);

namespace Meteia\Html;

class ClassVarianceAuthority implements ClassName
{
    public function __construct(private readonly array $core = [], private readonly array $variants = [])
    {
    }

    public function use(array $props): string
    {
        $activeVariants = array_map(fn ($variantName, $variant) => implode(' ', $this->variants[$variantName][$variant] ?? []), array_keys($props), $props);

        return implode(' ', [...$this->core, ...$activeVariants]);
    }

    public function attribute(array $props): ClassAttribute
    {
        return new ClassAttribute($this->use($props));
    }
}
