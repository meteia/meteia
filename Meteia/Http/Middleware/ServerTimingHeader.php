<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Performance\Timings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Meteia\Polyfills\array_map_assoc;

class ServerTimingHeader implements MiddlewareInterface
{
    public function __construct(private readonly Timings $timings)
    {
    }

    public function addHeader(ResponseInterface $response): ResponseInterface
    {
        $value = implode(',', array_map_assoc(fn ($key, $value) => [$key => $key . ';dur=' . round($value, 4)], $this->timings->all()));

        return $response->withHeader('Server-Timing', $value);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->addHeader($response);
    }
}
