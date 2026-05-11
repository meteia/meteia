<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;
use Override;

final readonly class Img implements Component
{
    public function __construct(
        public string $src,
        public ?string $alt = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $srcSet = null,
        public ?string $sizes = null,
        public ?string $className = null,
        public ?string $crossOrigin = null,
        public ?string $fetchPriority = null,
        public ?string $loading = null,
        public ?string $decoding = 'async',
    ) {}

    #[Override]
    public function render(): Node
    {
        return el('img', [
            'src' => $this->src,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'srcset' => $this->srcSet,
            'sizes' => $this->sizes,
            'class' => $this->className,
            'crossorigin' => $this->crossOrigin,
            'fetchpriority' => $this->fetchPriority,
            'loading' => $this->loading,
            'decoding' => $this->decoding,
        ]);
    }
}
