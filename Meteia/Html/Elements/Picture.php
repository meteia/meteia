<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;
use Override;

readonly class Picture implements Component
{
    /**
     * @param array<int, Source> $sources
     */
    public function __construct(
        public Img $img,
        public array $sources,
    ) {}

    #[Override]
    public function render(): Node
    {
        return el('picture', [], ...[...$this->sources, $this->img]);
    }
}
