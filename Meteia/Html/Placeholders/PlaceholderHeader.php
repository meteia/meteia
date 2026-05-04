<?php

declare(strict_types=1);

namespace Meteia\Html\Placeholders;

use Meteia\Bluestone\MutableString;
use Meteia\Html\Children;
use Meteia\Html\Header;
use Meteia\Html\Node;

class PlaceholderHeader extends MutableString implements Header
{
    #[\Override]
    public function title($title): Header
    {
        $this->set('<h1>' . $title . '</h1>');

        return $this;
    }

    #[\Override]
    public function render(): Node
    {
        return Children::of($this->rendered());
    }
}
