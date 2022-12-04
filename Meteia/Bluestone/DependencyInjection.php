<?php

declare(strict_types=1);

use Meteia\Bluestone\ImmutableString;

return [
    Stringable::class => fn () => new ImmutableString(),
];
