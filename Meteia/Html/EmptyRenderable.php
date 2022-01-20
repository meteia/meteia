<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Bluestone\Contracts\Renderable;

class EmptyRenderable implements Renderable
{
    public function __toString()
    {
        return '';
    }

    public function rendered(): string
    {
        return '';
    }
}
