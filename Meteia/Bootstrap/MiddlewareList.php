<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

use Meteia\Http\RequestHandler;
use NoDiscard;
use Psr\Http\Server\MiddlewareInterface;

final readonly class MiddlewareList
{
    /** @var array<int, MiddlewareInterface|class-string> */
    private array $items;

    /**
     * @param MiddlewareInterface|class-string ...$items
     */
    public function __construct(MiddlewareInterface|string ...$items)
    {
        $this->items = $items;
    }

    /**
     * @param MiddlewareInterface|class-string ...$middleware
     */
    #[NoDiscard]
    public function and(MiddlewareInterface|string ...$middleware): self
    {
        return new self(...$this->items, ...$middleware);
    }

    public function appendInto(RequestHandler $handler): void
    {
        $handler->append(...$this->items);
    }
}
