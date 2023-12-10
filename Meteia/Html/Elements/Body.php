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
    ) {
    }

    public function __toString(): string
    {
        return <<<EOF
            <body class="{$this->className}">
            {$this->header}
            <main>
                {$this->content}
            </main>
            {$this->footer}
            </body>
            EOF;
    }

    public function content(string|\Stringable $content): void
    {
        $this->content = $content;
    }
}
