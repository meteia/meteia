<?php

declare(strict_types=1);

namespace Meteia\Configuration;

interface Configuration
{
    public function string(string $name, string $default): string;

    public function int(string $name, int $default): int;

    public function boolean(string $name, bool $default): bool;

    public function float(string $name, float $default): float;
}
