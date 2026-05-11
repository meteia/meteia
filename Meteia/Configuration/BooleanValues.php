<?php

declare(strict_types=1);

namespace Meteia\Configuration;

use Meteia\Configuration\Errors\UnexpectedType;

class BooleanValues
{
    private const array FALSE_VALUES = ['false', 'off', 'no', 'n', '0', 0];
    private const array TRUE_VALUES = ['true', 'on', 'yes', 'y', '1', 1];

    public function boolean(string|int|bool $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);
        if (\in_array($value, self::TRUE_VALUES, true)) {
            return true;
        }
        if (\in_array($value, self::FALSE_VALUES, true)) {
            return false;
        }

        throw new UnexpectedType('Expected boolean-ish, got ' . $value);
    }
}
