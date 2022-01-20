<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

interface FrameFilter
{
    public function filter(array $frame): bool;
}
