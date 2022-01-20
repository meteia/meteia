<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

interface TypedData
{
    public function boolean(string $name, bool $default): bool;

    public function float(string $name, float $default): float;

    public function int(string $name, int $default): int;

    public function string(string $name, string $default): string;

    public function booleanOrThrow(string $name): bool;

    public function floatOrThrow(string $name): float;

    public function intOrThrow(string $name): int;

    public function stringOrThrow(string $name): string;

    public function array(string $name, array $default): array;
}
