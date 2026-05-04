<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\DependencyInjection\Container;
use Psr\Http\Server\RequestHandlerInterface;

interface Kernel
{
    public function run(MiddlewareList $middleware = new MiddlewareList()): void;

    /**
     * @param array<class-string, mixed> $definitions
     */
    public function container(array $definitions = []): Container;

    public function requestHandler(Container $container, MiddlewareList $middleware = new MiddlewareList()): RequestHandlerInterface;
}
