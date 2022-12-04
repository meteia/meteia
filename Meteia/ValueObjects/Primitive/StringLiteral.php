<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\ValueObjects\PrimitiveValueObject;
use Stringable;

abstract class StringLiteral extends PrimitiveValueObject implements Stringable
{
    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    public function compareTo(Comparable $other)
    {
        return strcasecmp($this->toNative(), $other->toNative());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
