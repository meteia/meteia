<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;

class Content implements Renderable
{
    use PhpTemplate;

    public function __construct(private string $name, private string $content)
    {
    }
}
