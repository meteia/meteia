<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\DependencyInjection\Container;
use Meteia\DependencyInjection\ContainerBuilder;
use Meteia\DependencyInjection\TimedContainer;
use Meteia\ErrorHandling\Dulce;
use Meteia\ErrorHandling\Endpoints\ErrorEndpoint;
use Meteia\Http\Middleware\PsrEndpoints;
use Meteia\Http\Middleware\ServerTimingHeader;
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
    ) {
    }

    public function run(array $middleware = []): void
    {
        // ob_start();
        $timings = new Timings();

        $applicationDefinitions = [
            ApplicationNamespace::class => $this->namespace,
            ApplicationPath::class => $this->path,
            ApplicationPublicDir::class => $this->publicDir,
            Timings::class => $timings,
        ];

        /** @var Container $container */
        $container = $timings->measure(
            'di.init',
            fn () => ContainerBuilder::build($this->path, $this->namespace, $applicationDefinitions),
        );
        $container = new TimedContainer($timings, $container);

        Dulce::onFatalError($container, function (\Throwable $throwable) use ($applicationDefinitions): void {
            // A fresh container is needed to clear out any previous state, layout rendering in particular
            $freshContainer = ContainerBuilder::build($this->path, $this->namespace, [
                \Throwable::class => $throwable,
                ...$applicationDefinitions,
            ]);
            $errorEndpoint = $freshContainer->get(ErrorEndpoint::class);
            $response = $freshContainer->call([$errorEndpoint, 'response'], [$throwable]);
            send($response);
        });

        /** @var RequestHandlerInterface $requestHandler */
        $requestHandler = $container->get(RequestHandlerInterface::class);
        $requestHandler->append(ServerTimingHeader::class);
        $requestHandler->append(...$middleware);
        $requestHandler->append(PsrEndpoints::class);

        $serverRequest = $container->get(ServerRequestInterface::class);

        $response = $requestHandler->handle($serverRequest);

        send($response);
    }
}
