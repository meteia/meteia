<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\DependencyInjection\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class SetServerRequestInterface implements MiddlewareInterface
{
    public function __construct(
        private Container $container,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->container->set(ServerRequestInterface::class, $request);

        return $handler->handle($request);
    }
}
