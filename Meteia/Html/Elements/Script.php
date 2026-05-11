<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\HeadResource;
use Meteia\Html\Node;
use Override;

final readonly class Script implements Component, HeadResource
{
    public function __construct(
        public string $src,
        public bool $async = false,
        public bool $defer = false,
        public string $type = '',
        public string $integrity = '',
        public string $crossorigin = '',
    ) {}

    #[Override]
    public function render(): Node
    {
        return el('script', [
            'src' => $this->src,
            'async' => $this->async,
            'defer' => $this->defer,
            'type' => $this->type,
            'integrity' => $this->integrity,
            'crossorigin' => $this->crossorigin,
        ]);
    }

    #[Override]
    public function addTo(Head $head): void
    {
        $head->scripts->add($this);
    }
}
