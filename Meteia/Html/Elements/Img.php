<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Element;

readonly class Img
{
    use Element;

    protected string $decoding;

    public function __construct(
        protected string $src,
        protected ?string $alt = null,
        protected ?int $width = null,
        protected ?int $height = null,
        protected ?string $srcset = null,
        protected ?string $sizes = null,
        protected ?string $class = null,
        protected ?string $crossorigin = null,
        protected ?string $fetchpriority = null,
        protected ?string $loading = null,
    ) {
        $this->decoding = 'async';
    }
}
