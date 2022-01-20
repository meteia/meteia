<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Script;

class Scripts implements \IteratorAggregate
{
    private $scripts = [];

    public function getIterator(): \Traversable
    {
        foreach ($this->scripts as $script) {
            yield $script;
        }
    }

    public function load($src, $async = false, $defer = false, string $integrity = '', string $crossorigin = ''): void
    {
        $this->scripts[] = new Script($src, $async, $defer, '', $integrity, $crossorigin);
    }

    public function module($src): void
    {
        $this->scripts[] = new Script($src, false, false, 'module');
    }
}
