<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\CustomElement;

readonly class Button extends CustomElement
{
    /**
     * @param array<string, string|\Stringable|number|boolean> $attributes
     */
    public function __construct(array $attributes = [], null|string|\Stringable $children = null)
    {
        parent::__construct('button', $attributes, $children);
    }
}
