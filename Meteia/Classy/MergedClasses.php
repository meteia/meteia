<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Generator;
use Override;

final readonly class MergedClasses implements Classes
{
    /** @var list<Classes> */
    private array $sources;

    public function __construct(Classes ...$sources)
    {
        $this->sources = array_values($sources);
    }

    #[Override]
    public function getIterator(): Generator
    {
        foreach ($this->sources as $source) {
            yield from $source;
        }
    }
}
