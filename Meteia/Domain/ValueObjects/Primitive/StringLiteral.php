<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class StringLiteral extends ImmutablePrimitiveValueObject implements Comparable
{
    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

    public function compareTo(Comparable $other)
    {
        return strcasecmp($this->toNative(), $other->toNative());
    }

    public function __toString()
    {
        return $this->value;
    }
}
