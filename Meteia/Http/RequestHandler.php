<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\DependencyInjection\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface, MiddlewareInterface
{
    private $middleware = [];

    public function __construct(private Container $container)
    {
    }

    public function append(MiddlewareInterface|string ...$middleware): self
    {
        array_push($this->middleware, ...$middleware);

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this);
    }

    public function prepend(MiddlewareInterface|string ...$middleware): self
    {
        array_unshift($this->middleware, ...$middleware);

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = array_shift($this->middleware);
        if ($middleware === null) {
            throw new \Exception('Request was not handled by any middleware');
        }
        if (\is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        return $middleware->process($request, $this);
    }
}
