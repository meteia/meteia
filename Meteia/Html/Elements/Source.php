<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\VoidElement;

class Source
{
    use VoidElement;

    public function __construct(
        protected string $type,
        protected ?string $src = null,
        protected ?string $srcset = null,
        protected ?string $sizes = null,
        protected ?string $media = null,
        protected ?string $width = null,
        protected ?string $height = null,
    ) {
    }
}
