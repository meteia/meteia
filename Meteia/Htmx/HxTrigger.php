<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Meteia\Html\Attribute;
use Stringable;

class HxTrigger extends Attribute
{
    public function __construct(bool|Stringable|string $value)
    {
        parent::__construct('hx-trigger', $value);
    }
}
