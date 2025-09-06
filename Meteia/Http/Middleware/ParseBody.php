<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParseBody implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isJsonBody = array_any($request->getHeader('Content-Type'), static fn($ct) => str_contains(
            $ct,
            'application/json',
        ));
        if ($isJsonBody) {
            $body = $request->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $contents = $body->getContents();
            try {
                $json = json_decode($contents, true, 256, JSON_THROW_ON_ERROR);
                $request = $request->withParsedBody($json);
            } catch (\JsonException) {
                // Ignore JSON parse errors
            }
        }

        return $handler->handle($request);
    }
}
