<?php

declare(strict_types=1);

namespace Meteia\Http\Middleware;

use Meteia\DependencyInjection\Container;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
final class SeedMessageScopeTest extends TestCase
{
    public function testHonorsAValidCorrelationIdHeader(): void
    {
        $known = CorrelationId::random();
        $request = new Psr17Factory()->createServerRequest('GET', '/')->withHeader('X-Correlation-ID', (string) $known);
        $sink = $this->captureScope();

        $response = new SeedMessageScope($this->container(), ProcessId::random())->process($request, $sink);

        static::assertNotNull($sink->observed);
        static::assertSame((string) $known, (string) $sink->observed->correlationId());
        static::assertSame((string) $known, $response->getHeaderLine('X-Correlation-ID'));
    }

    public function testMintsFreshCorrelationIdWhenHeaderIsMalformed(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/')->withHeader('X-Correlation-ID', 'not a token');
        $sink = $this->captureScope();

        $response = new SeedMessageScope($this->container(), ProcessId::random())->process($request, $sink);

        static::assertNotNull($sink->observed);
        static::assertStringStartsWith('crr_', $response->getHeaderLine('X-Correlation-ID'));
    }

    public function testMintsFreshCorrelationIdWhenHeaderIsAbsent(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/');
        $sink = $this->captureScope();

        $response = new SeedMessageScope($this->container(), ProcessId::random())->process($request, $sink);

        static::assertNotNull($sink->observed);
        $minted = $response->getHeaderLine('X-Correlation-ID');
        static::assertStringStartsWith('crr_', $minted);
        static::assertSame($minted, (string) $sink->observed->correlationId());
    }

    public function testCausationIsDistinctFromCorrelation(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/');
        $sink = $this->captureScope();

        new SeedMessageScope($this->container(), ProcessId::random())->process($request, $sink);

        static::assertNotNull($sink->observed);
        static::assertNotSame((string) $sink->observed->correlationId(), (string) $sink->observed->causationId());
    }

    public function testPublishesMessageScopeOntoTheContainer(): void
    {
        $container = $this->container();
        $request = new Psr17Factory()->createServerRequest('GET', '/');

        new SeedMessageScope($container, ProcessId::random())->process($request, $this->captureScope());

        static::assertInstanceOf(MessageScope::class, $container->get(MessageScope::class));
    }

    private function captureScope(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public ?MessageScope $observed = null;

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $scope = $request->getAttribute(MessageScope::class);
                $this->observed = $scope instanceof MessageScope ? $scope : null;

                return new Response(204);
            }
        };
    }

    private function container(): Container
    {
        return new class implements Container {
            /** @var array<string, mixed> */
            private array $bindings = [];

            #[\Override]
            public function get(string $id): mixed
            {
                return $this->bindings[$id] ?? null;
            }

            #[\Override]
            public function has(string $id): bool
            {
                return isset($this->bindings[$id]);
            }

            #[\Override]
            public function set(string $id, mixed $value): void
            {
                $this->bindings[$id] = $value;
            }

            #[\Override]
            public function call($callable, array $parameters = []): mixed
            {
                return $callable(...$parameters);
            }
        };
    }
}
