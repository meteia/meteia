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
    public function use(array $props): string
    {
        $props = array_merge($this->defaultVariants, $props);

        $classes = [];

        // Add base classes
        $classes = array_merge($classes, explode(' ', $this->class));

        // Add variant classes
        foreach ($this->variants as $variantName => $variantOptions) {
            if (isset($props[$variantName]) && isset($variantOptions[$props[$variantName]])) {
                $variantClass = $variantOptions[$props[$variantName]];
                if (is_array($variantClass)) {
                    $classes = array_merge($classes, $variantClass);
                } elseif (is_string($variantClass)) {
                    $classes = array_merge($classes, explode(' ', $variantClass));
                }
            }
        }

        // Add compound variant classes
        foreach ($this->compoundVariants as $compound) {
            $matches = true;
            foreach ($compound as $key => $value) {
                if ($key === 'class' || $key === 'className') {
                    continue;
                }
                if (!isset($props[$key]) || $props[$key] !== $value) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                $compoundClass = $compound['class'] ?? $compound['className'] ?? '';
                if (is_array($compoundClass)) {
                    $classes = array_merge($classes, $compoundClass);
                } elseif (is_string($compoundClass)) {
                    $classes = array_merge($classes, explode(' ', $compoundClass));
                }
            }
        }

        // Clean and unique
        $classes = array_map('trim', $classes);
        $classes = array_filter($classes, static fn($c) => $c !== '');
        $classes = array_unique($classes);

        return implode(' ', $classes);
    }

    #[\Override]
    public function attribute(array $props): ClassAttribute
    {
        return new ClassAttribute($this->use($props));
    }
}
