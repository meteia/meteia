<?php

declare(strict_types=1);

namespace Meteia\Html;

interface ClassName
{
    public function use(array $props): string;

    public function attribute(array $props): ClassAttribute;
}
