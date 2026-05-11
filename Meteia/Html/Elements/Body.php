<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Component;
use Meteia\Html\Footer;
use Meteia\Html\Header;
use Meteia\Html\Node;
use Override;
use Stringable;

class Body implements Component
{
    /**
     * @param array<string, Stringable|null|scalar> $attributes
     */
    public function __construct(
        public Header $header,
        public Footer $footer,
        public array $attributes = [],
        public string|Stringable|Component $content = '',
    ) {}

    #[Override]
    public function render(): Node
    {
        return el('body', $this->attributes, $this->header, el('main', [], $this->content), $this->footer);
    }

    public function content(string|Stringable|Component $content): void
    {
        $this->content = $content;
    }
}
