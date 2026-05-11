<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Head;

interface HeadResource
{
    public function addTo(Head $head): void;
}
