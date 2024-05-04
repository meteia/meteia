<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\CustomElement;

class Button extends CustomElement
{
    public function __construct(array $attributes = [], null|string|\Stringable $children = null)
    {
        parent::__construct('button', $attributes, $children);
    }
}
