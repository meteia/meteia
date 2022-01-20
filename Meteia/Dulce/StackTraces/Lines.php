<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

class Lines implements \IteratorAggregate
{
    public function __construct()
    {
    }

    public function getIterator()
    {
        yield '';
    }
}
