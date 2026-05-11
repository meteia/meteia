<?php

declare(strict_types=1);

namespace Meteia\Polyfills;

use Exception;
use stdClass;

/**
 * @source: http://stackoverflow.com/a/15973172
 *
 * @param array<array-key, array<array-key, mixed>> $input
 *
 * @return list<array<array-key, mixed>>
 */
function array_cartesian(array $input): array
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
 *
 * @param array<array-key, mixed> $a
 *
 * @return array<array-key, mixed>
 */
function array_map_assoc(callable $f, array $a): array
{
    $mapped = array_map($f, array_keys($a), $a);

    /** @var list<array<array-key, mixed>> $mapped */
    return array_replace([], ...$mapped);
}

/**
 * @param array<array-key, mixed> $a
 */
function array_end(array $a): mixed
{
    return end($a);
}

/**
 * @template T
 *
 * @param array<array-key, T> $toSort
 * @param list<int|string> $order
 * @param callable(T): (int|string) $comparedValue
 *
 * @return array<array-key, T>
 */
function array_reorder(array $toSort, array $order, callable $comparedValue): array
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
 * @param array<array-key, mixed> $array
 *
 * @return array<array-key, mixed>|stdClass
 */
function array_to_object(array $array): array|stdClass
{
    $resultObj = new stdClass();
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
            throw new Exception(
                'Current level has both integer and string keys, thus it is impossible to keep array or convert to object',
            );
        }
        if ($hasStrKeys) {
            $resultObj->{(string) $k} = \is_array($v) ? array_to_object($v) : $v;
        } else {
            $resultArr[$k] = \is_array($v) ? array_to_object($v) : $v;
        }
    }

    return $hasStrKeys ? $resultObj : $resultArr;
}

/**
 * @template T
 *
 * @param array<array-key, T> $array
 * @param (callable(T, array-key): bool)|null $callback
 */
function array_first(array $array, ?callable $callback = null, mixed $default = null): mixed
{
    if ($callback === null) {
        if (empty($array)) {
            return $default;
        }

        foreach ($array as $item) {
            return $item;
        }

        return $default;
    }

    foreach ($array as $key => $value) {
        if ($callback($value, $key)) {
            return $value;
        }
    }

    return $default;
}
