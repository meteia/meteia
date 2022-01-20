<?php

declare(strict_types=1);

namespace Meteia\Http\ServerRequestBodies;

interface ServerRequestBody
{
    public function string($key, string $default): string;

    public function int($key, int $default): int;
}
