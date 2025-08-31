<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Http\Cookies\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SetCookieMiddleware implements MiddlewareInterface
{
    /**
     * @var Cookie[]
     */
    private $cookies = [];

    public function __construct() {}

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response = array_reduce($this->cookies, static fn($response, Cookie $cookie) => $response, $response);

        return $response;
    }

    public function set(Cookie $cookie): void
    {
        $this->cookies[] = $cookie;
    }
}
