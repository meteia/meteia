<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Performance\Timings;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ServerTimingHeader implements MiddlewareInterface
{
    public function __construct(
        private readonly Timings $timings,
    ) {}

    public function addHeader(ResponseInterface $response): ResponseInterface
    {
        $parts = [];
        foreach ($this->timings->all() as $key => $duration) {
            $parts[] = $key . ';dur=' . round($duration, 4);
        }
        $value = implode(',', $parts);

        return $response->withHeader('Server-Timing', $value);
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $this->addHeader($response);
    }
}
