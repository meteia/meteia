<?php

declare(strict_types=1);

namespace Meteia\Html\Placeholders;

use Meteia\Bluestone\MutableString;
use Meteia\Html\Header;

class PlaceholderHeader extends MutableString implements Header
{
    #[\Override]
    public function title($title): Header
    {
        $this->replaceContentWith('<h1>' . $title . '</h1>');

        return $this;
    }
}
