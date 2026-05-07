<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use ArrayAccess;
use Countable;

interface ArrayValueObject extends \IteratorAggregate, Countable, ArrayAccess
{
}
