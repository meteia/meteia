<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;

class Form
{
    use PhpTemplate;

    public function __construct(private string $action, private string $method, private Renderable $content)
    {
    }
}
