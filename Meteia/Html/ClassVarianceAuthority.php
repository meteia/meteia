<?php

declare(strict_types=1);

namespace Meteia\Html;

class ClassVarianceAuthority implements ClassName
{
    public function __construct(
        private readonly string $class = '',
        private readonly array $variants = [],
    ) {}

    #[\Override]
    public function use(array $props): string
    {
        $activeVariants = array_map(
            fn($variantName, $variant) => $this->variants[$variantName][$variant] ?? '',
            array_keys($props),
            $props,
        );

        $coreClassNames = explode(' ', $this->class);
        $activeVariantClassNames = explode(' ', implode(' ', $activeVariants));
        $allClassNames = [...$coreClassNames, ...$activeVariantClassNames];
        $allClassNames = array_map('trim', $allClassNames);
        $allClassNames = array_filter($allClassNames, static fn($className) => $className !== '');
        $uniqueClassNames = array_unique($allClassNames);

        return implode(' ', $uniqueClassNames);
    }

    #[\Override]
    public function attribute(array $props): ClassAttribute
    {
        return new ClassAttribute($this->use($props));
    }
}
