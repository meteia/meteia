<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\VoidElement;

readonly class Source
{
    use VoidElement;

    public function __construct(
        public string $type,
        public ?string $src = null,
        public ?string $srcSet = null,
        public ?string $sizes = null,
        public ?string $media = null,
        public ?string $width = null,
        public ?string $height = null,
    ) {
    }
}
