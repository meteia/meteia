<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Footer;
use Meteia\Html\Header;

class Body implements \Stringable
{
    /**
     * @param array<string, string|\Stringable|number|boolean> $attributes
     */
    public function __construct(
        public Header $header,
        public Footer $footer,
        public array $attributes = [],
        public string|\Stringable $content = '',
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return el('body', $this->attributes, $this->header, el('main', [], $this->content), $this->footer);
    }

    public function content(string|\Stringable $content): void
    {
        $this->content = $content;
    }
}
