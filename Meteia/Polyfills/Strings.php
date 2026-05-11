<?php

declare(strict_types=1);

namespace Meteia\Polyfills;

/**
 * @param list<string> $regexs
 */
function in_regexs(string $string, array $regexs): bool
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
 *
 * @param list<string> $strings
 */
function common_prefix_length(array $strings): int
{
    if (\count($strings) < 2) {
        return 0;
    }
    sort($strings);

    $s1 = (string) array_shift($strings);
    $s2 = (string) array_pop($strings);
    $len = min(\strlen($s1), \strlen($s2));

    $i = 0;
    while ($i < $len && $s1[$i] === $s2[$i]) {
        ++$i;
    }

    return $i;
}

/**
 * @param list<string> $strings
 *
 * @return list<string>
 */
function remove_common_prefix(array $strings): array
{
    $prefixLength = common_prefix_length($strings);

    return array_values(array_map(static fn(string $string) => substr($string, $prefixLength), $strings));
}

function without_prefix(string $string, string $prefix): string
{
    $prefixLength = common_prefix_length([$string, $prefix]);

    return substr($string, $prefixLength);
}
