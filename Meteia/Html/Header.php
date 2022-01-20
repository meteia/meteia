<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Bluestone\Contracts\Renderable;

interface Header extends Renderable
{
    public function title($title): Header;
}
