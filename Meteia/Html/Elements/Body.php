<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Footer;
use Meteia\Html\Header;
use Stringable;

class Body implements Stringable
{
    public function __construct(
        public Header $header,
        public Stringable $content,
        public Footer $footer,
        public string $className = '',
    ) {
    }

    public function __toString(): string
    {
        return <<<EOF
            <body class="$this->className">
            $this->header
            <main>
                $this->content
            </main>
            $this->footer
            </body>
            EOF;
    }

    public function content(Stringable $content): void
    {
        $this->content = $content;
    }
}
