<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ServerRequestInterface;

class RequestCookies
{
    public function __construct(
        private readonly ServerRequestInterface $serverRequest,
    ) {}

    public function plaintext(string $name, string $default): PlaintextCookie
    {
        $cookies = $this->serverRequest->getCookieParams();

        return new PlaintextCookie($name, $cookies[$name] ?? $default);
    }

    public function sealed(string $name): SealedCookie
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            throw new \RuntimeException("Cookie '$name' not found");
        }

        return new SealedCookie($name, $cookies[$name]);
    }
}
