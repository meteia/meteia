<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Node;

class Form implements Component
{
    public function __construct(
        private readonly string $action,
        private readonly string $method,
        private readonly string|\Stringable|Component $content,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return el('form', ['action' => $this->action, 'method' => $this->method], $this->content);
    }
}
