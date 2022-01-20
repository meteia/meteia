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
    /**
     * @var Timings
     */
    private $timings;

    public function __construct(Timings $timings)
    {
        $this->timings = $timings;
    }

    public function addHeader(ResponseInterface $response): ResponseInterface
    {
        $value = implode(',', array_map_assoc(function ($key, $value) {
            return [$key => $key . ';dur=' . round($value)];
        }, $this->timings->all()));

        return $response->withHeader('Server-Timing', $value);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->addHeader($response);
    }
}
