<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Html\Elements\Body;
use Meteia\Html\Elements\Head;

use function Meteia\Html\Elements\el;

class HtmlLayout implements Layout
{
    public function __construct(
        private readonly Head $head,
        private readonly Body $body,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return Children::of('<!DOCTYPE html>', el('html', ['lang' => 'en'], $this->head, $this->body));
    }

    #[\Override]
    public function body(): Body
    {
        return $this->body;
    }

    #[\Override]
    public function head(): Head
    {
        return $this->head;
    }
}
