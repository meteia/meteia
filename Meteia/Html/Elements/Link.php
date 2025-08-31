<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\VoidElement;

class Link implements \Stringable
{
    use VoidElement;

    public function __construct(
        public string $rel,
        public string|\Stringable $href,
        public ?string $integrity = null,
        public ?string $crossorigin = null,
        public ?string $sizes = null,
        public ?string $type = null,
    ) {}
}
