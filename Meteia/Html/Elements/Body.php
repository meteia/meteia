<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;
use Meteia\Html\Footer;
use Meteia\Html\Header;

class Body
{
    use PhpTemplate;

    public function __construct(public Header $header, public Renderable $content, public Footer $footer)
    {
    }

    public function content(Renderable $content): void
    {
        $this->content = $content;
    }
}
