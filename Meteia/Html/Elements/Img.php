<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;

readonly class Img
{
    use Element;

    public function __construct(
        public string $src,
        public ?string $alt = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $srcSet = null,
        public ?string $sizes = null,
        public ?string $className = null,
        public ?string $crossOrigin = null,
        public ?string $fetchpriority = null,
        public ?string $loading = null,
        public ?string $decoding = 'async',
    ) {}
}
