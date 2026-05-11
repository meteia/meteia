<?php

declare(strict_types=1);

namespace Meteia\Http;

use IteratorAggregate;
use Meteia\DependencyInjection\Container;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Traversable;

/**
 * @implements IteratorAggregate<int, MiddlewareInterface>
 */
class MiddlewareStack implements IteratorAggregate
{
    /**
     * @param list<MiddlewareInterface|class-string<MiddlewareInterface>> $middleware
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $middleware = [],
    ) {}

    /**
     * @return Traversable<int, MiddlewareInterface>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->middleware as $middleware) {
            if (\is_string($middleware)) {
                $resolved = $this->container->get($middleware);
                \assert($resolved instanceof MiddlewareInterface);
                yield $resolved;

                continue;
            }

            yield $middleware;
        }
    }
}
