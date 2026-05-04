<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Node;

final readonly class Source implements Node
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

    #[\Override]
    public function __toString(): string
    {
        return (string) el('source', [
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
