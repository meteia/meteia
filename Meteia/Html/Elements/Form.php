<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Bluestone\PhpTemplate;
use Stringable;

class Form
{
    use PhpTemplate;

    public function __construct(private string $action, private string $method, private Stringable $content)
    {
    }
}
