<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;

final readonly class Script implements Component
{
    public function __construct(
        public string $src,
        public bool $async = false,
        public bool $defer = false,
        public string $type = '',
        public string $integrity = '',
        public string $crossorigin = '',
    ) {}

    #[\Override]
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
}
