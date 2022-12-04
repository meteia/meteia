<?php

declare(strict_types=1);

namespace Meteia\Polyfills;

function in_regexs(string $string, array $regexs)
{
    foreach ($regexs as $regex) {
        if (preg_match($regex, $string) === 1) {
            return true;
        }
    }

    return false;
}

/**
 * @see https://stackoverflow.com/a/35838357/31341
 */
function common_prefix_length(array $strings): int
{
    if (count($strings) < 2) {
        return 0;
    }
    sort($strings);

    $s1 = array_shift($strings);
    $s2 = array_pop($strings);
    $len = min(strlen($s1), strlen($s2));

    // While we still have string to compare,
    // if the indexed character is the same in both strings,
    // increment the index.
    for ($i = 0; $i < $len && $s1[$i] === $s2[$i]; ++$i) {
    }

    return $i;
}

function remove_common_prefix(array $strings): array
{
    $prefixLength = common_prefix_length($strings);

    return array_map(fn (string $string) => substr($string, $prefixLength), $strings);
}

function without_prefix(string $string, string $prefix): string
{
    $prefixLength = common_prefix_length([$string, $prefix]);

    return substr($string, $prefixLength);
}
