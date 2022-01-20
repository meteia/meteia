<?php

declare(strict_types=1);

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\ImmutableString;

return [
    Renderable::class => function () {
        return new ImmutableString();
    },
];
