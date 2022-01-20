<?php

declare(strict_types=1);

namespace Meteia\Configuration;

use Meteia\Configuration\Errors\UnexpectedType;

class EnvironmentConfiguration implements Configuration
{
    private const BOOLEAN_VALUES_FALSE = ['false', 'off', 'no', 'n', '0'];
    private const BOOLEAN_VALUES_TRUE = ['true', 'on', 'yes', 'y', '1'];

    public function boolean(string $name, bool $default): bool
    {
        $value = $_ENV[$name] ?? null;
        if ($value === null) {
            return $default;
        }
        $value = strtolower($value);
        if (\in_array($value, self::BOOLEAN_VALUES_TRUE, true)) {
            return true;
        }
        if (\in_array($value, self::BOOLEAN_VALUES_FALSE, true)) {
            return false;
        }

        throw new UnexpectedType('Expected boolean, got ' . $value);
    }

    public function float(string $name, float $default): float
    {
        $value = $_ENV[$name] ?? null;
        if ($value === null) {
            return $default;
        }
        if (!is_numeric($value)) {
            throw new UnexpectedType('Expected float, got ' . $value);
        }

        return (float) $value;
    }

    public function int(string $name, int $default): int
    {
        $value = $_ENV[$name] ?? null;
        if ($value === null) {
            return $default;
        }

        if (!is_numeric($value) || bccomp((string) (int) $value, (string) (float) $value, 6) !== 0) {
            throw new UnexpectedType('Expected int, got ' . $value);
        }

        return (int) $value;
    }

    public function string(string $name, string $default): string
    {
        $value = $_ENV[$name] ?? null;
        if ($value === null) {
            return $default;
        }

        return $value;
    }
}
