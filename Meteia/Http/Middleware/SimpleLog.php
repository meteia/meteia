<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\Http\Configuration\LogPath;
use Meteia\Http\RequestBody;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

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
        $line .= 'Headers  : ' . json_encode($request->getHeaders()) . PHP_EOL;
        $line .= 'Form Data  : ' . json_encode($_POST) . PHP_EOL;
        if (strlen($this->requestBody->content())) {
            $line .= '  ' . $this->requestBody->content() . PHP_EOL;
        }
        file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

        try {
            $response = $handler->handle($request);
        } catch (Throwable $th) {
            $line = sprintf('  <-- %s', $th->getCode()) . PHP_EOL;
            $line .= 'Exception  : ' . $th->getMessage() . PHP_EOL;
            $line .= 'Stack trace: ' . $th->getTraceAsString() . PHP_EOL . PHP_EOL;
            file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

            throw $th;
        }

        $line = sprintf('  <-- %s', $response->getStatusCode()) . PHP_EOL;
        $line .= 'Headers  : ' . json_encode($response->getHeaders()) . PHP_EOL;
        $line .= 'Response : ' . $response->getBody()->getContents() . PHP_EOL;
        file_put_contents((string) $logFile, $line, FILE_APPEND | LOCK_EX);

        return $response;
    }
}
