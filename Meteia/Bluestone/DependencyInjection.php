<?php

declare(strict_types=1);

use Meteia\Bluestone\ImmutableString;

return [
    Stringable::class => function () {
        return new ImmutableString();
    },
];
