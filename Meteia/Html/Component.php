<?php

declare(strict_types=1);

namespace Meteia\Html;

interface Component
{
    #[\NoDiscard]
    public function render(): Node;
}
