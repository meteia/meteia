<?php

declare(strict_types=1);

namespace Meteia\Html;

use Override;

class ClassVarianceAuthority implements ClassName
{
    /**
     * @param string $class Base class names
     * @param array<string, array<string, array<int, string>|string>> $variants Variant definitions
     * @param list<array<string, mixed>> $compoundVariants Compound variant definitions
     * @param array<string, mixed> $defaultVariants Default variant values
     */
    public function __construct(
        private readonly string $class = '',
        private readonly array $variants = [],
        private readonly array $compoundVariants = [],
        private readonly array $defaultVariants = [],
    ) {}

    #[Override]
    public function use(array $props): ClassList
    {
        /** @var array<string, mixed> $props */
        $props = array_merge($this->defaultVariants, $props);

        $list = ClassList::of($this->class);

        foreach ($this->variants as $variantName => $variantOptions) {
            if (!isset($props[$variantName])) {
                continue;
            }
            $selector = $props[$variantName];
            \assert(\is_string($selector) || \is_int($selector));
            if (!isset($variantOptions[$selector])) {
                continue;
            }
            $option = $variantOptions[$selector];
            \assert(\is_string($option) || \is_array($option));
            $list = $list->merge(self::asList($option));
        }

        foreach ($this->compoundVariants as $compound) {
            if (!self::matches($compound, $props)) {
                continue;
            }
            $cls = $compound['class'] ?? $compound['className'] ?? '';
            \assert(\is_string($cls) || \is_array($cls));
            /** @var array<int, string>|string $cls */
            $list = $list->merge(self::asList($cls));
        }

        return $list;
    }

    #[Override]
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
