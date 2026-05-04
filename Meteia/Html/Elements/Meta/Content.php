<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Html\Component;
use Meteia\Html\Node;

use function Meteia\Html\Elements\el;

readonly class Content implements Component
{
    public function __construct(
        private string|\Stringable $name,
        private string|\Stringable $content,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('meta', ['name' => (string) $this->name, 'content' => (string) $this->content]);
    }
}
