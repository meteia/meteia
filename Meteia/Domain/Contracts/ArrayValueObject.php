<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface ArrayValueObject extends IteratorAggregate, Countable, ArrayAccess
{
}
