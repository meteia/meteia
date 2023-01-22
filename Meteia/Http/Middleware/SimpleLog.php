<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Http\Configuration\LogPath;
use Meteia\Http\RequestBody;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SimpleLog implements MiddlewareInterface
{
    public function __construct(
        private readonly RequestBody $requestBody,
        private readonly LogPath $logPath,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $logFile = $this->logPath->join('http.log');
        $line = PHP_EOL . sprintf('--> %s %s', $request->getMethod(), $request->getUri()) . PHP_EOL;
        if (strlen($this->requestBody->content())) {
            $line .= '  ' . $this->requestBody->content() . PHP_EOL;
        }
        file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

        $response = $handler->handle($request);

        $line = sprintf('  <-- %s', $response->getStatusCode()) . PHP_EOL;
        file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

        return $response;
    }
}
