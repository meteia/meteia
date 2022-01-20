<?php

declare(strict_types=1);

namespace Meteia\Http\Cookies;

use Psr\Http\Message\ServerRequestInterface;

class Cookies
{
    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    public function string(string $name, string $default): string
    {
        $cookies = $this->serverRequest->getCookieParams();
        if (!isset($cookies[$name])) {
            return $default;
        }

        return $cookies[$name] ?? $default;
    }
}
