<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Link;

class Stylesheets implements \Stringable
{
    private array $stylesheets = [];

    public function __toString()
    {
        return implode('', $this->stylesheets);
    }

    public function load($href, string $integrity = null, string $crossorigin = null): void
    {
        $this->stylesheets[$href] = new Link('stylesheet', $href, $integrity, $crossorigin);
    }
}
