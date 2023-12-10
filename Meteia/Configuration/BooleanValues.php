<?php

declare(strict_types=1);

namespace Meteia\Configuration;

use Meteia\Configuration\Errors\UnexpectedType;

class BooleanValues
{
    private const BOOLEAN_VALUES_FALSE = ['false', 'off', 'no', 'n', '0'];
    private const BOOLEAN_VALUES_TRUE = ['true', 'on', 'yes', 'y', '1'];

    public function boolean(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);
        if (\in_array($value, self::BOOLEAN_VALUES_TRUE, true)) {
            return true;
        }
        if (\in_array($value, self::BOOLEAN_VALUES_FALSE, true)) {
            return false;
        }

        throw new UnexpectedType('Expected boolean, got ' . $value);
    }
}
