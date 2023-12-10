<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts;

use IteratorAggregate;

interface ValueObject extends \JsonSerializable, IteratorAggregate
{
}
