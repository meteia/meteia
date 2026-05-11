<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\HeadResource;
use Meteia\Html\Node;
use Override;
use Stringable;

final readonly class Link implements Component, HeadResource
{
    public function __construct(
        public string $rel,
        public string|Stringable $href,
        public ?string $integrity = null,
        public ?string $crossorigin = null,
        public ?string $sizes = null,
        public ?string $type = null,
    ) {}

    #[Override]
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

    #[Override]
    public function addTo(Head $head): void
    {
        $head->stylesheets->add($this);
    }
}
