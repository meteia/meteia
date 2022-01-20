<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Body;

interface HasBody
{
    public function body(): Body;
}
