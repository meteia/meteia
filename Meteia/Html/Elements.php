<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

function html(string $raw): string
{
    return htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
}

function attributes(array $attributes): string
{
    if (\count($attributes) === 0) {
        return '';
    }
    $attributes = array_filter($attributes, static fn($val) => !($val === null || $val === false));
    $attributes = array_map(
        static function ($k, $v) {
            if (\is_bool($v) && $v) {
                return $k;
            }

            return \sprintf('%s="%s"', $k, html((string) $v));
        },
        array_keys($attributes),
        $attributes,
    );

    return implode(' ', $attributes);
}

function el(string $name, ?array $attributes = [], string|\Stringable ...$children): string
{
    if (\count($attributes) === 1 && isset($attributes[0]) && \is_string($attributes[0])) {
        $attributes = ['class' => $attributes[0]];
    }
    $attributes = attributes($attributes);
    $children = implode(PHP_EOL, $children);

    return "<{$name} {$attributes}>{$children}</{$name}>";
}
