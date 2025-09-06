<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

readonly class PlaintextCookie
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}
}
