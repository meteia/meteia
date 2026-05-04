<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Html\Component;
use Meteia\Html\Node;

use function Meteia\Html\Elements\el;

readonly class ItemProp implements Component
{
    public function __construct(
        private string|\Stringable $name,
        private string|\Stringable $content,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('meta', ['itemprop' => (string) $this->name, 'content' => (string) $this->content]);
    }
}
