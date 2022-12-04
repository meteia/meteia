<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;
use Stringable;

class Button
{
    use Element;

    public function __construct(
        public Stringable|string $children,
        public ?string $class = null,
    ) {
    }
}
