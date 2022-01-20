<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Performance\Timings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParseBody implements MiddlewareInterface
{
    public function __construct(Timings $timings)
    {
        $this->timings = $timings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ct = implode('', $request->getHeader('Content-Type'));
        switch ($ct) {
            case 'application/json':
                $contents = $request->getBody()->getContents();
                $json = json_decode($contents, true, 256, JSON_THROW_ON_ERROR);
                $request = $request->withParsedBody($json);
        }

        return $handler->handle($request);
    }
}
