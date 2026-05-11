<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\StackTraces;

use Generator;
use IteratorAggregate;
use Override;

/**
 * @implements IteratorAggregate<int, string>
 */
class Lines implements IteratorAggregate
{
    public function __construct() {}

    #[Override]
    public function getIterator(): Generator
    {
        yield '';
    }
}
