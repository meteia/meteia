<?php

declare(strict_types=1);

namespace Meteia\Html\Placeholders;

use Meteia\Bluestone\MutableString;
use Meteia\Html\Children;
use Meteia\Html\Footer;
use Meteia\Html\Node;

class PlaceholderFooter extends MutableString implements Footer
{
    #[\Override]
    public function render(): Node
    {
        return Children::of($this->rendered());
    }
}
