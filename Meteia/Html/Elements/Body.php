<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\PhpTemplate;
use Meteia\Html\Footer;
use Meteia\Html\Header;
use Stringable;

class Body
{
    use PhpTemplate;

    public function __construct(
        public Header $header,
        public Stringable $content,
        public Footer $footer,
        public string $className = '',
    ) {
    }

    public function content(Stringable $content): void
    {
        $this->content = $content;
    }
}
