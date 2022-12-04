<?php

declare(strict_types=1);

namespace Meteia\Html;

trait DataAttributes
{
    public function attrs(...$names): string
    {
        $attrs = get_object_vars($this);
        $attrs = array_filter($attrs, fn ($key) => in_array($key, $names, true), ARRAY_FILTER_USE_KEY);
        $attrs = array_map(
            function ($k, $v) {
                $k = preg_replace('~(?<=\\w)([A-Z])~u', '-$1', $k);
                $k = mb_strtolower($k);

                if (is_bool($v) && $v) {
                    return 'data-' . $k;
                }

                return sprintf('data-%s="%s"', $k, $v);
            },
            array_keys($attrs),
            $attrs,
        );
        $attrs = implode(' ', $attrs);

        return $attrs;
    }
}
