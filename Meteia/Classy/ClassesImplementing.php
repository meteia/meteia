<?php

declare(strict_types=1);

namespace Meteia\Classy;

final readonly class ClassesImplementing implements Classes
{
    public function __construct(
        private Classes $classes,
        private string $implementing,
    ) {}

    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->classes as $class) {
            if (!is_subclass_of($class, $this->implementing)) {
                continue;
            }

            yield $class;
        }
    }
}
