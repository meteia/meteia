<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

function attributes(array $attributes): string
{
    if (count($attributes) === 0) {
        return '';
    }
    $attributes = array_filter($attributes, fn ($val) => !empty($val));
    $attributes = array_map(
        function ($k, $v) {
            if (is_bool($v) && $v) {
                return $k;
            }

            return sprintf('%s="%s"', $k, htmlentities((string) $v, ENT_HTML5 | ENT_COMPAT, 'UTF-8'));
        },
        array_keys($attributes),
        $attributes,
    );

    return implode(' ', $attributes);
}

function el(string $name, array $attributes, string ...$children)
{
    $attributes = attributes($attributes);
    $children = implode(PHP_EOL, $children);

    return "<$name $attributes>$children</$name>";
}
