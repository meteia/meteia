<?php

declare(strict_types=1);

namespace Meteia\Html;

use Meteia\Bluestone\PhpTemplate;
use Meteia\Html\Elements\Body;
use Meteia\Html\Elements\Head;

class HtmlLayout implements Layout
{
    use PhpTemplate;

    public function __construct(private Head $head, private Body $body)
    {
    }

    public function body(): Body
    {
        return $this->body;
    }

    public function head(): Head
    {
        return $this->head;
    }
}
