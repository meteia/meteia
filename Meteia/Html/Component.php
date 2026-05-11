<?php

declare(strict_types=1);

namespace Meteia\Html;

use NoDiscard;

interface Component
{
    #[NoDiscard]
    public function render(): Node;
}
