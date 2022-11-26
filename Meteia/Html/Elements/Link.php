<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;

class Link
{
    use Element;

    public function __construct(
        public string $rel,
        public string $href,
        public ?string $integrity,
        public ?string $crossorigin,
    ) {
    }
}
