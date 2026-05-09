<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

readonly class PlaintextCookie
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}
}
