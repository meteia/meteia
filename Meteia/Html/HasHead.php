<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Head;

interface HasHead
{
    public function head(): Head;
}
