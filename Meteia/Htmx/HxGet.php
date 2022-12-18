<?php

declare(strict_types=1);

namespace Meteia\Htmx;

use Meteia\Html\Attribute;

class HxGet extends Attribute
{
    public function __construct(string $url)
    {
        parent::__construct('hx-get', $url);
    }
}
