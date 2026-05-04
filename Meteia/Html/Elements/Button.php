<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Node;

final readonly class Button implements Node
{
    /**
     * @param array<int|string, mixed> $attributes
     */
    public function __construct(
        public array $attributes = [],
        public null|string|\Stringable $children = null,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return (string) el('button', $this->attributes, $this->children ?? '');
    }
}
