<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Meteia\Html\Attribute;

class HxTrigger extends Attribute
{
    public function __construct(bool|string|\Stringable $value)
    {
        parent::__construct('hx-trigger', $value);
    }
}
