<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;

final readonly class Link implements Component
{
    public function __construct(
        public string $rel,
        public string|\Stringable $href,
        public ?string $integrity = null,
        public ?string $crossorigin = null,
        public ?string $sizes = null,
        public ?string $type = null,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('link', [
            'rel' => $this->rel,
            'href' => (string) $this->href,
            'integrity' => $this->integrity,
            'crossorigin' => $this->crossorigin,
            'sizes' => $this->sizes,
            'type' => $this->type,
        ]);
    }
}
