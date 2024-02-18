<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling;

use Meteia\ErrorHandling\ErrorClassifications\ErrorClassification;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Dulce
{
    public function __construct(private LoggerInterface $logger, private ErrorClassification $errorClassification)
    {
    }

    public static function onFatalError(ContainerInterface|InvokerInterface $container, callable $callback): void
    {
        register_shutdown_function(static function (...$args) use ($container, $callback): void {
            /** @var self $errorHandler */
            $errorHandler = $container->get(Dulce::class);
            $errorHandler->onShutdown($callback);
        });
        // set_error_handler(function (...$args) use ($container, $callback): void {
        //    /** @var self $errorHandler */
        //    $errorHandler = $container->get(ErrorHandling::class);
        //    $args[] = $callback;
        //    $errorHandler->onError(...$args);
        // });
        set_exception_handler(static function (\Throwable $throwable) use ($callback): void {
            $callback($throwable);
        });
    }

    private function onError(int $errno, string $errstr, string $errfile, int $errline, callable $callback): void
    {
        $error = new \ErrorException($errstr, $errno, 1, $errfile, $errline);
        if ($this->errorClassification->isFatal($errno)) {
            $this->onThrowable($error, $callback);
        }
    }

    private function onThrowable(\Throwable $throwable, callable $callback): void
    {
        $this->logger->error($throwable->getMessage(), [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ]);
        $callback($throwable);
    }

    private function onShutdown(callable $callback): void
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $thrownError = new \ErrorException($error['message'], $error['type'], 1, $error['file'], $error['line']);
        if ($this->errorClassification->isFatal($error['type'])) {
            $this->onThrowable($thrownError, $callback);
        }
    }
}
