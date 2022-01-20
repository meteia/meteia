<?php

declare(strict_types=1);

namespace Meteia\Html;

use IteratorAggregate;
use Meteia\Bluestone\Contracts\Renderable;
use Traversable;

class Metadata implements IteratorAggregate
{
    private $tags = [];

    public function getIterator(): Traversable
    {
        foreach ($this->tags as $tag) {
            yield $tag;
        }
    }

    public function include(Renderable $view): void
    {
        $this->tags[] = $view;
    }
}
