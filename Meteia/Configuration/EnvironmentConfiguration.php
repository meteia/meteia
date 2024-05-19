<?php

declare(strict_types=1);

namespace Meteia\Configuration;

use Dotenv\Dotenv;
use Meteia\Configuration\Errors\UnexpectedType;

readonly class EnvironmentConfiguration implements Configuration
{
    private const array BOOLEAN_VALUES_FALSE = ['false', 'off', 'no', 'n', '0'];
    private const array BOOLEAN_VALUES_TRUE = ['true', 'on', 'yes', 'y', '1'];

    private array $env;

    public function __construct()
    {
        $this->env = match (isset($_ENV['APP_ENV_FILES'])) {
            true => array_merge($_ENV, ...array_map(
                static fn (string $file) => Dotenv::parse(file_get_contents($file)),
                array_filter(
                    explode(',', $_ENV['APP_ENV_FILES']),
                    static fn (string $file) => file_exists($file) && is_readable($file),
                ),
            )),
            default => $_ENV,
        };
    }

    public function boolean(string $name, bool $default): bool
    {
        $value = $this->env[$name] ?? null;
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
        $value = $this->env[$name] ?? null;
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
        $value = $this->env[$name] ?? null;
        if ($value === null) {
            return $default;
        }

        if (!is_numeric($value) || bccomp((string) (int) $value, (string) (float) $value, 6) !== 0) {
            throw new UnexpectedType('Expected int, got ' . $value);
        }

        return (int) $value;
    }

    public function string(string $name, string|\Stringable $default): string
    {
        $value = $this->env[$name] ?? null;
        if ($value === null) {
            return (string) $default;
        }

        return $value;
    }
}
