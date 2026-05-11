<?php

declare(strict_types=1);

namespace Meteia\Html;

use Override;
use Stringable;

class Metadata implements Component
{
    /** @var list<string|\Stringable|Component> */
    private array $tags = [];

    #[Override]
    public function render(): Node
    {
        return Children::of(...$this->tags);
    }

    public function include(Stringable|Component $view): void
    {
        $this->tags[] = $view;
    }
}
