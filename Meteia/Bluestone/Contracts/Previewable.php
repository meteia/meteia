<?php

declare(strict_types=1);

namespace Meteia\Bluestone\Contracts;

interface Previewable
{
    public function preview(): string;
}
