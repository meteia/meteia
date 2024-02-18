<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Templates;

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
