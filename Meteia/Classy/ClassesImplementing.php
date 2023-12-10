<?php

declare(strict_types=1);

namespace Meteia\Classy;

class ClassesImplementing implements \IteratorAggregate
{
    public function __construct(private readonly iterable $classes, private readonly string $implementing)
    {
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->classes as $class) {
            if (is_subclass_of($class, $this->implementing)) {
                yield $class;
            }
        }
    }
}
