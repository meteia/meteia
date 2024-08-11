<?php

declare(strict_types=1);

namespace Meteia\Polyfills;

/**
 * @source: http://stackoverflow.com/a/15973172
 *
 * @param mixed $input
 */
function array_cartesian($input)
{
    $input = array_filter($input);

    $result = [[]];

    foreach ($input as $key => $values) {
        $append = [];

        foreach ($result as $product) {
            foreach ($values as $item) {
                $product[$key] = $item;
                $append[] = $product;
            }
        }

        $result = $append;
    }

    return $result;
}

/**
 * Using array_replace due to https://stackoverflow.com/q/17462354.
 *
 * @source https://stackoverflow.com/a/43004994
 */
function array_map_assoc(callable $f, array $a)
{
    return array_replace([], ...array_map($f, array_keys($a), $a));
}

function array_pluck_assoc(array $a, string $name): array
{
    return array_combine(array_pluck($a, $name), $a);
}

function array_end(array $a)
{
    return end($a);
}

function array_reorder(array $toSort, array $order, callable $comparedValue)
{
    $targetOrder = array_flip($order);
    usort($toSort, static function ($a, $b) use ($targetOrder, $comparedValue) {
        $left = $targetOrder[$comparedValue($a)];
        $right = $targetOrder[$comparedValue($b)];

        return $left <=> $right;
    });

    return $toSort;
}

/**
 * @source: http://stackoverflow.com/a/21650726/31341
 *
 * @param mixed $array
 */
function array_to_object($array)
{
    $resultObj = new \stdClass();
    $resultArr = [];
    $hasIntKeys = false;
    $hasStrKeys = false;
    foreach ($array as $k => $v) {
        if (!$hasIntKeys) {
            $hasIntKeys = \is_int($k);
        }
        if (!$hasStrKeys) {
            $hasStrKeys = \is_string($k);
        }
        if ($hasIntKeys && $hasStrKeys) {
            $e = new \Exception(
                'Current level has both integer and string keys, thus it is impossible to keep array or convert to object',
            );
            $e->vars = ['level' => $array];

            throw $e;
        }
        if ($hasStrKeys) {
            $resultObj->{$k} = \is_array($v) ? array_to_object($v) : $v;
        } else {
            $resultArr[$k] = \is_array($v) ? array_to_object($v) : $v;
        }
    }

    return $hasStrKeys ? $resultObj : $resultArr;
}

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
