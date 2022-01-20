<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Bluestone\Contracts\Renderable;
use Meteia\Bluestone\PhpTemplate;

class Charset implements Renderable
{
    use PhpTemplate;

    public function __construct(private string $characterSet = 'UTF-8')
    {
    }
}
