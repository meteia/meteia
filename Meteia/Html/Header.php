<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

interface Header extends Stringable
{
    public function title($title): Header;
}
