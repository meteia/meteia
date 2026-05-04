<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;

final readonly class Button implements Component
{
    /**
     * @param array<int|string, mixed> $attributes
     */
    public function __construct(
        public array $attributes = [],
        public null|string|\Stringable|Component $children = null,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('button', $this->attributes, $this->children ?? '');
    }
}
