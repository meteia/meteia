<?php

declare(strict_types=1);

namespace Meteia\Dulce\Templates;

use Meteia\Bluestone\PhpTemplate;

class MissingFrame
{
    use PhpTemplate;

    public int $line = 0;

    public string $file = 'unknown';

    public function __construct()
    {
    }
}
