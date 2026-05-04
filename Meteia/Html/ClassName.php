<?php

declare(strict_types=1);

namespace Meteia\Html;

interface ClassName
{
    /**
     * @param array<string, mixed> $props
     */
    public function use(array $props): ClassList;

    /**
     * @param array<string, mixed> $props
     */
    public function attribute(array $props): ClassAttribute;
}
