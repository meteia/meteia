<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Script;
use Stringable;

class Scripts implements Stringable
{
    private array $scripts = [];

    public function __toString()
    {
        return implode('', $this->scripts);
    }

    public function load($src, $async = false, $defer = false, string $integrity = '', string $crossorigin = ''): void
    {
        $this->scripts[$src] = new Script($src, $async, $defer, '', $integrity, $crossorigin);
    }

    public function module($src): void
    {
        $this->scripts[$src] = new Script($src, false, false, 'module');
    }
}
