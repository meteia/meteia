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

        $requestHandler = $this->requestHandler($container, $middleware);
        $serverRequest = $container->get(ServerRequestInterface::class);
        \assert($serverRequest instanceof ServerRequestInterface, 'ServerRequestInterface is bound at request boundary');
        $response = $requestHandler->handle($serverRequest);

        $this->sink->send($response);

        $this->flushUnitOfWork($container);
    }

    private function flushUnitOfWork(Container $container): void
    {
        try {
            $unitOfWork = $container->get(UnitOfWork::class);
            \assert($unitOfWork instanceof UnitOfWork, 'UnitOfWork binding resolves to UnitOfWork instance');
            $scope = $container->get(MessageScope::class);
            \assert($scope instanceof MessageScope, 'MessageScope is seeded by SeedMessageScope middleware');
            $unitOfWork->complete($scope);
        } catch (Throwable $error) {
            try {
                $log = $container->get(LoggerInterface::class);
                \assert($log instanceof LoggerInterface, 'LoggerInterface is bound by Logging DependencyInjection');
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

        /** @var Container $container */
        $container = $timings->measure('di.init', fn() => ContainerBuilder::build(
            $this->path,
            $this->namespace,
            $applicationDefinitions,
        ));

        return new TimedContainer($timings, $container);
    }

    #[Override]
    public function requestHandler(
        Container $container,
        MiddlewareList $middleware = new MiddlewareList(),
    ): RequestHandlerInterface {
        /** @var RequestHandler $requestHandler */
        $requestHandler = $container->get(RequestHandlerInterface::class);

        $requestHandler->append(SeedMessageScope::class);
        $requestHandler->append(new CatchAndReportErrors($this));
        $requestHandler->append(ServerTimingHeader::class);
        $requestHandler->append(ResponseCookies::class);
        $middleware->appendInto($requestHandler);
        $requestHandler->append(PsrEndpoints::class);

        return $requestHandler;
    }
}
