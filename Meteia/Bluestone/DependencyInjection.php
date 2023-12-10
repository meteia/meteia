<?php

declare(strict_types=1);

use Meteia\Bluestone\ImmutableString;

return [
    Stringable::class => static fn () => new ImmutableString(),
];
