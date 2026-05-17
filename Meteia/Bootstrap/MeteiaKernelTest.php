<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\DependencyInjection\Container;
use Meteia\Http\ResponseSink;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Override;

/**
 * @internal
 */
final class MeteiaKernelTest extends TestCase
{
    public function testReleaseRequestResourcesReleasesContainerResources(): void
    {
        $resources = new class implements RequestResources {
            public bool $released = false;

            #[Override]
            public function release(): void
            {
                $this->released = true;
            }
        };
        $container = $this->createStub(Container::class);
        $container->method('get')->willReturnCallback(
            static fn(string $id): mixed => match ($id) {
                RequestResources::class => $resources,
                default => throw new RuntimeException('Unexpected container value: ' . $id),
            },
        );

        $releaseRequestResources = new ReflectionMethod(MeteiaKernel::class, 'releaseRequestResources');
        $releaseRequestResources->invoke($this->kernel(), $container);

        static::assertTrue($resources->released);
    }

    private function kernel(): MeteiaKernel
    {
        return new MeteiaKernel(
            new ApplicationNamespace('App'),
            new ApplicationPath('.'),
            new ApplicationPublicDir('public'),
            $this->createStub(ResponseSink::class),
        );
    }
}
