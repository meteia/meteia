<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use IteratorAggregate;
use JsonSerializable;

interface ValueObject extends JsonSerializable, IteratorAggregate
{
}
