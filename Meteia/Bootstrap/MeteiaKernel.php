<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\DependencyInjection\TimedContainer;
use Meteia\ErrorHandling\Middleware\CatchAndReportErrors;
use Meteia\Http\Middleware\PsrEndpoints;
use Meteia\Http\Middleware\ResponseCookies;
use Meteia\Http\Middleware\SeedMessageScope;
use Meteia\Http\Middleware\ServerTimingHeader;
use Meteia\Http\PsrResponseSink;
use Meteia\Http\RequestHandler;
use Meteia\Http\ResponseSink;
use Meteia\Performance\Timings;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class MeteiaKernel implements Kernel
{
    public function __construct(
        private ApplicationNamespace $namespace,
        private ApplicationPath $path,
        private ApplicationPublicDir $publicDir,
        private ResponseSink $sink = new PsrResponseSink(),
    ) {}

    #[\Override]
    public function run(MiddlewareList $middleware = new MiddlewareList()): void
    {
        $container = $this->container();

        $requestHandler = $this->requestHandler($container, $middleware);
        $serverRequest = $container->get(ServerRequestInterface::class);
        $response = $requestHandler->handle($serverRequest);

        $this->sink->send($response);
    }

    #[\Override]
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

    #[\Override]
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
