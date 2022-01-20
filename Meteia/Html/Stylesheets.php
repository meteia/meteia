<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Link;

class Stylesheets implements \IteratorAggregate
{
    private $stylesheets = [];

    public function getIterator(): \Traversable
    {
        foreach ($this->stylesheets as $stylesheet) {
            yield $stylesheet;
        }
    }

    public function load($href, ?string $integrity = null, ?string $crossorigin = null): void
    {
        $this->stylesheets[] = new Link('stylesheet', $href, $integrity, $crossorigin);
    }
}
