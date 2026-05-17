<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\DependencyInjection\TimedContainer;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\ErrorHandling\Middleware\CatchAndReportErrors;
use Meteia\Http\Middleware\PsrEndpoints;
use Meteia\Http\Middleware\ResponseCookies;
use Meteia\Http\Middleware\SeedMessageScope;
use Meteia\Http\Middleware\ServerTimingHeader;
use Meteia\Http\PsrResponseSink;
use Meteia\Http\RequestHandler;
use Meteia\Http\ResponseSink;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use UnexpectedValueException;

final readonly class MeteiaKernel implements Kernel
{
    public function __construct(
        private ApplicationNamespace $namespace,
        private ApplicationPath $path,
        private ApplicationPublicDir $publicDir,
        private ResponseSink $sink = new PsrResponseSink(),
    ) {}

    #[Override]
    public function run(MiddlewareList $middleware = new MiddlewareList()): void
    {
        $container = $this->container();

        try {
            $requestHandler = $this->requestHandler($container, $middleware);
            $serverRequest = $this->resolved(
                $container->get(ServerRequestInterface::class),
                ServerRequestInterface::class,
                'ServerRequestInterface is bound at request boundary',
            );
            $response = $requestHandler->handle($serverRequest);

            $this->sink->send($response);

            $this->flushUnitOfWork($container);
        } finally {
            $this->releaseRequestResources($container);
        }
    }

    private function flushUnitOfWork(Container $container): void
    {
        try {
            $unitOfWork = $this->resolved(
                $container->get(UnitOfWork::class),
                UnitOfWork::class,
                'UnitOfWork binding resolves to UnitOfWork instance',
            );
            $scope = $this->resolved(
                $container->get(MessageScope::class),
                MessageScope::class,
                'MessageScope is seeded by SeedMessageScope middleware',
            );
            $unitOfWork->complete($scope);
        } catch (Throwable $error) {
            try {
                $log = $this->resolved(
                    $container->get(LoggerInterface::class),
                    LoggerInterface::class,
                    'LoggerInterface is bound by Logging DependencyInjection',
                );
                $log->error('UnitOfWork flush failed after response', [
                    'exception' => $error::class,
                    'message' => $error->getMessage(),
                ]);
                // @mago-expect lint:no-empty-catch-clause -- response already sent; logging is best-effort.
            } catch (Throwable) {
            }
        }
    }

    #[Override]
    public function container(array $definitions = []): Container
    {
        $timings = new Timings();

        $applicationDefinitions = [
            ApplicationNamespace::class => $this->namespace,
            ApplicationPath::class => $this->path,
            ApplicationPublicDir::class => $this->publicDir,
            Timings::class => $timings,
            ...$definitions,
        ];

        return new TimedContainer(
            $timings,
            $this->resolved(
                $timings->measure('di.init', fn() => ContainerBuilder::build(
                    $this->path,
                    $this->namespace,
                    $applicationDefinitions,
                )),
                Container::class,
                'ContainerBuilder must return a Container',
            ),
        );
    }

    #[Override]
    public function requestHandler(
        Container $container,
        MiddlewareList $middleware = new MiddlewareList(),
    ): RequestHandlerInterface {
        $requestHandler = $this->resolved(
            $container->get(RequestHandlerInterface::class),
            RequestHandler::class,
            'RequestHandlerInterface binding must resolve to RequestHandler',
        );

        $requestHandler->append(SeedMessageScope::class);
        $requestHandler->append(new CatchAndReportErrors($this));
        $requestHandler->append(ServerTimingHeader::class);
        $requestHandler->append(ResponseCookies::class);
        $middleware->appendInto($requestHandler);
        $requestHandler->append(PsrEndpoints::class);

        return $requestHandler;
    }

    private function releaseRequestResources(Container $container): void
    {
        try {
            $this->resolved(
                $container->get(RequestResources::class),
                RequestResources::class,
                'RequestResources binding must resolve to RequestResources',
            )->release();
        } catch (Throwable) {
            return;
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    private function resolved(mixed $value, string $type, string $message): object
    {
        if ($value instanceof $type) {
            return $value;
        }

        throw new UnexpectedValueException($message);
    }
}
