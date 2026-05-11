<?php

declare(strict_types=1);

namespace Meteia\Html;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use NoDiscard;
use Override;
use Stringable;

/**
 * @implements \IteratorAggregate<int, string|\Stringable|Component>
 */
final readonly class Children implements Node, IteratorAggregate
{
    /**
     * @param list<string|\Stringable|Component> $nodes
     */
    public function __construct(
        public array $nodes = [],
    ) {}

    public static function of(string|Stringable|Component ...$nodes): self
    {
        return new self(array_values($nodes));
    }

    /**
     * @param callable(): (string|\Stringable|Component|iterable<string|\Stringable|Component>) $produce
     */
    #[NoDiscard]
    public function when(bool $condition, callable $produce): self
    {
        if (!$condition) {
            return $this;
        }
        $produced = $produce();
        $more = is_iterable($produced) && !$produced instanceof Stringable ? [...$produced] : [$produced];

        return clone($this, ['nodes' => [...$this->nodes, ...$more]]);
    }

    /**
     * @param iterable<mixed> $items
     * @param callable(mixed, int|string): (string|\Stringable|Component) $render
     */
    #[NoDiscard]
    public function each(iterable $items, callable $render): self
    {
        $more = [];
        foreach ($items as $key => $item) {
            $more[] = $render($item, $key);
        }

        return clone($this, ['nodes' => [...$this->nodes, ...$more]]);
    }

    #[NoDiscard]
    public function append(string|Stringable|Component ...$more): self
    {
        return clone($this, ['nodes' => [...$this->nodes, ...array_values($more)]]);
    }

    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->nodes);
    }

    #[Override]
    public function __toString(): string
    {
        return new HtmlEncoder()->encode($this);
    }
}
