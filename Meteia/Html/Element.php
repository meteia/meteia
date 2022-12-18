<?php

declare(strict_types=1);

namespace Meteia\Html;

trait Element
{
    public function __toString(): string
    {
        $tagName = explode('\\', get_called_class());
        $tagName = array_pop($tagName);
        $tagName = strtolower($tagName);

        $originalAttrs = array_filter(get_object_vars($this), fn ($val) => !empty($val));
        $attrs = array_filter($originalAttrs, fn ($value, $key) => $key !== 'children', ARRAY_FILTER_USE_BOTH);
        $attrs = array_map(
            function ($k, $v) {
                if (is_bool($v) && $v) {
                    return $k;
                }

                return sprintf('%s="%s"', $k, $v);
            },
            array_keys($attrs),
            $attrs,
        );
        $attrs = implode(' ', $attrs);

        return sprintf('<%s %s>%s</%s>' . PHP_EOL, $tagName, $attrs, $originalAttrs['children'] ?? '', $tagName);
    }
}
