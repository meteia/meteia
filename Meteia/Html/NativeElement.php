<?php

declare(strict_types=1);

namespace Meteia\Html;

interface NativeElement
{
    public function instance(
        array $props = [],
        array $attributes = [],
        string|\Stringable $children = null,
    ): string|Stringable;
}
