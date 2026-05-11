<?php

declare(strict_types=1);

namespace Meteia\Html;

trait DataAttributes
{
    public function attrs(...$names): string
    {
        $attrs = get_object_vars($this);
        $attrs = array_filter($attrs, static fn($key) => \in_array($key, $names, true), ARRAY_FILTER_USE_KEY);
        $attrs = array_map(
            static function ($k, $v): string {
                $k = preg_replace('~(?<=\\w)([A-Z])~u', '-$1', (string) $k);
                \assert(\is_string($k));
                $k = mb_strtolower($k);

                if (\is_bool($v) && $v) {
                    return 'data-' . $k;
                }

                \assert(\is_scalar($v) || $v === null || $v instanceof \Stringable);

                return sprintf('data-%s="%s"', $k, (string) $v);
            },
            array_keys($attrs),
            $attrs,
        );

        return implode(' ', $attrs);
    }
}
