<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;
use Meteia\ValueObjects\Identity\Uri;
use Stringable;

class A
{
    use Element;

    public function __construct(
        public Stringable|string $children,
        public ?Uri $href,
        public ?string $class = null,
    ) {
    }
}
