<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Footer;
use Meteia\Html\Header;

class Body implements \Stringable
{
    public function __construct(
        public Header $header,
        public Footer $footer,
        public string|\Stringable $content = '',
        public string $className = '',
        public array $attributes = [],
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return el(
            'body',
            [
                'class' => $this->className,
                ...$this->attributes,
            ],
            $this->header,
            el('main', [], $this->content),
            $this->footer,
        );
    }

    public function content(string|\Stringable $content): void
    {
        $this->content = $content;
    }
}
