<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Bluestone\PhpTemplate;
use Stringable;

class Charset implements Stringable
{
    use PhpTemplate;

    public function __construct(private string $characterSet = 'UTF-8')
    {
    }
}
