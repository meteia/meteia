<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\DependencyInjection\TimedContainer;
use Meteia\ErrorHandling\Middleware\CatchAndReportErrors;
use Meteia\Http\Middleware\PsrEndpoints;
use Meteia\Http\Middleware\ResponseCookies;
use Meteia\Http\Middleware\ServerTimingHeader;
use Meteia\Http\RequestHandler;
use Meteia\Performance\Timings;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Meteia\Http\Functions\send;

readonly class Instance
{
    public function __construct(
        private ApplicationNamespace $namespace,
        private ApplicationPath $path,
        private ApplicationPublicDir $publicDir,
    ) {}

    public function run(array $middleware = []): void
    {
        $container = $this->container();

        $requestHandler = $this->requestHandler($container, $middleware);
        $serverRequest = $container->get(ServerRequestInterface::class);
        $response = $requestHandler->handle($serverRequest);

        send($response);
    }

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

    public function requestHandler(Container $container, array $middleware = []): RequestHandlerInterface
    {
        /** @var RequestHandler $requestHandler */
        $requestHandler = $container->get(RequestHandlerInterface::class);

        $requestHandler->append(new CatchAndReportErrors($this));
        $requestHandler->append(ServerTimingHeader::class);
        $requestHandler->append(ResponseCookies::class);
        $requestHandler->append(...$middleware);
        $requestHandler->append(PsrEndpoints::class);

        return $requestHandler;
    }
}
