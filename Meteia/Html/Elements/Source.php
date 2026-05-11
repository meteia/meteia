<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;
use Override;

final readonly class Source implements Component
{
    public function __construct(
        public string $type,
        public ?string $src = null,
        public ?string $srcSet = null,
        public ?string $sizes = null,
        public ?string $media = null,
        public ?string $width = null,
        public ?string $height = null,
    ) {}

    #[Override]
    public function render(): Node
    {
        return el('source', [
            'type' => $this->type,
            'src' => $this->src,
            'srcset' => $this->srcSet,
            'sizes' => $this->sizes,
            'media' => $this->media,
            'width' => $this->width,
            'height' => $this->height,
        ]);
    }
}
