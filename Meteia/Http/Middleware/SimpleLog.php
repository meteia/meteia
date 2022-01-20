<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Application\RepositoryPath;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SimpleLog implements MiddlewareInterface
{
    public function __construct(
        private RepositoryPath $repositoryPath,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $logFile = $this->repositoryPath->join('http.log');
        $line = sprintf('%s %s', $request->getMethod(), $request->getUri()) . PHP_EOL;
        file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

        return $handler->handle($request);
    }
}
