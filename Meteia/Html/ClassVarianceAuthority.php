<?php

declare(strict_types=1);

namespace Meteia\Html;

class ClassVarianceAuthority implements ClassName
{
    /**
     * @param string $class Base class names
     * @param array $variants Variant definitions
     * @param array $compoundVariants Compound variant definitions
     * @param array $defaultVariants Default variant values
     */
    public function __construct(
        private readonly string $class = '',
        private readonly array $variants = [],
        private readonly array $compoundVariants = [],
        private readonly array $defaultVariants = [],
    ) {}

    #[\Override]
    public function use(array $props): ClassList
    {
        $props = array_merge($this->defaultVariants, $props);

        $list = ClassList::of($this->class);

        foreach ($this->variants as $variantName => $variantOptions) {
            if (!isset($props[$variantName], $variantOptions[$props[$variantName]])) {
                continue;
            }
            $list = $list->merge(self::asList($variantOptions[$props[$variantName]]));
        }

        foreach ($this->compoundVariants as $compound) {
            if (!self::matches($compound, $props)) {
                continue;
            }
            $list = $list->merge(self::asList($compound['class'] ?? $compound['className'] ?? ''));
        }

        return $list;
    }

    #[\Override]
    public function attribute(array $props): ClassAttribute
    {
        return new ClassAttribute($this->use($props));
    }

    /**
     * @param array<int, string>|string $value
     */
    private static function asList(array|string $value): ClassList
    {
        return \is_array($value) ? ClassList::of(implode(' ', $value)) : ClassList::of($value);
    }

    /**
     * @param array<string, mixed> $compound
     * @param array<string, mixed> $props
     */
    private static function matches(array $compound, array $props): bool
    {
        foreach ($compound as $key => $value) {
            if ($key === 'class' || $key === 'className') {
                continue;
            }
            if (!isset($props[$key]) || $props[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
