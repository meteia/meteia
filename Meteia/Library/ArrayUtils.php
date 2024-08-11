<?php

declare(strict_types=1);

namespace Meteia\Library;

function array_first(array $array, ?callable $callback = null, $default = null)
{
    if ($callback === null) {
        if (empty($array)) {
            return $default;
        }

        foreach ($array as $item) {
            return $item;
        }
    }

    foreach ($array as $key => $value) {
        if ($callback($value, $key)) {
            return $value;
        }
    }

    return $default;
}
