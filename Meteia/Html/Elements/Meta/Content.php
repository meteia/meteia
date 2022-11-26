<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Bluestone\PhpTemplate;
use Stringable;

class Content implements Stringable
{
    use PhpTemplate;

    public function __construct(private readonly string $name, private readonly string $content)
    {
    }
}
