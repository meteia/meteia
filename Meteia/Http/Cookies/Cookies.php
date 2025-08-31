<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ServerRequestInterface;

class Cookies
{
    public function __construct(
        private readonly ServerRequestInterface $serverRequest,
    ) {}

    public function string(string $name, string $default): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }

        return $cookies[$name] ?? $default;
    }
}
