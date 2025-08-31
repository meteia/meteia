<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;

class Script
{
    use Element;

    public function __construct(
        public string $src,
        public bool $async = false,
        public bool $defer = false,
        public string $type = '',
        public string $integrity = '',
        public string $crossorigin = '',
    ) {}
}
