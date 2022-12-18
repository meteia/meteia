<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\CustomElement;
use Stringable;

class Button extends CustomElement
{
    public function __construct(array $attributes = [], Stringable|string|null $children = null)
    {
        parent::__construct('button', $attributes, $children);
    }
}
