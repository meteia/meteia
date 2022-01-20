<?php

declare(strict_types=1);

namespace Http;

use Psr\Http\Server\MiddlewareInterface;
use Traversable;

class MiddlewareStack implements \IteratorAggregate
{
    /**
     * @return Traversable<MiddlewareInterface>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->middleware as $middleware) {
            if (is_string($middleware)) {
                $middleware = $this->container->get($middleware);
            }
            yield $middleware;
        }
    }
}
