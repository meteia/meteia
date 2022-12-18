<?php

declare(strict_types=1);

namespace Meteia\Html;

use Stringable;

interface NativeElement
{
    public function instance(
        array $props = [],
        array $attributes = [],
        Stringable|string|null $children = null,
    ): string|Stringable;
}
