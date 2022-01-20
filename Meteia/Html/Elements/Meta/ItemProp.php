<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

use Meteia\Bluestone\PhpTemplate;

class ItemProp
{
    use PhpTemplate;

    public function __construct(private string $name, private string $content)
    {
    }
}
