<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Middleware;

use Meteia\Application\Instance;
use Meteia\ErrorHandling\DulceErrorException;
use Meteia\ErrorHandling\Endpoints\ErrorEndpoint;
use Meteia\ErrorHandling\ErrorClassifications\ErrorClassification;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function Meteia\Http\Functions\send;

class CatchAndReportErrors implements MiddlewareInterface
{
    private LoggerInterface $logger;

    private ErrorClassification $errorClassification;

    public function __construct(
        private Instance $instance,
    ) {
        $container = $instance->container();
        $this->logger = $container->get(LoggerInterface::class);
        $this->errorClassification = $container->get(ErrorClassification::class);

        $this->registerFatalErrorHandlers();
    }

    private function registerFatalErrorHandlers(): void
    {
        register_shutdown_function([$this, 'onShutdown']);
        set_error_handler([$this, 'onError']);
        set_exception_handler([$this, 'handleThrowableGlobal']);
    }

    public function onError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        if ($this->errorClassification->isFatal($errno)) {
            $error = new DulceErrorException($errstr, $errno, 1, $errfile, $errline);
            $this->handleThrowableGlobal($error);
        }
    }

    public function onShutdown(): void
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $thrownError = new DulceErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
        if ($this->errorClassification->isFatal($error['type'])) {
            $this->handleThrowableGlobal($thrownError);
        }
    }

    public function handleThrowableGlobal(\Throwable $throwable): void
    {
        $response = $this->handleThrowable($throwable);
        send($response);
        exit();
    }

    private function handleError(\Throwable $throwable): void
    {
        $this->logger->error($throwable->getMessage(), [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ]);
    }

    private function handleThrowable(\Throwable $throwable): ResponseInterface
    {
        $this->handleError($throwable);

        $freshContainer = $this->instance->container([
            \Throwable::class => $throwable,
        ]);
        $errorEndpoint = $freshContainer->get(ErrorEndpoint::class);

        return $freshContainer->call([
            $errorEndpoint,
            'response',
        ], [$throwable]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $throwable) {
            return $this->handleThrowable($throwable);
        }
    }
}
