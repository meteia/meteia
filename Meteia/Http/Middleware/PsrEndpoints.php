<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Classy\BestMatchingClass;
use Meteia\DependencyInjection\Container;
use Meteia\Http\Endpoint;
use Meteia\Http\EndpointMap;
use Meteia\Http\HomepageEndpoint;
use Meteia\Performance\Timings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrEndpoints implements MiddlewareInterface
{
    public function __construct(
        private readonly Container $container,
        private readonly EndpointMap $endpointMap,
        private readonly BestMatchingClass $bestMatchingClass,
        private readonly Timings $timings,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $endpoint = $this->timings->measure('endpoint.lookup', fn () => $this->endpoint($request));

        return $this->timings->measure('endpoint.response', fn () => $endpoint->response($request));
    }

    private function endpoint(ServerRequestInterface $request): Endpoint
    {
        $path = $request->getUri()->getPath();
        if ($path === '/') {
            // FIXME: This feels hackish
            return $this->container->get(HomepageEndpoint::class);
        }

        // FIXME: This is code path is very slow
        $className = $this->endpointMap->classNameFor($path);
        $bestMatchingClass = $this->bestMatchingClass->in($className, Endpoint::class, ['\\Index']);

        return $this->container->get($bestMatchingClass);
    }
}
