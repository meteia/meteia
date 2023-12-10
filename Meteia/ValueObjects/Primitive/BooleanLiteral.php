<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\ValueObjects\PrimitiveValueObject;

abstract class BooleanLiteral extends PrimitiveValueObject implements Comparable
{
    public function __construct($value)
    {
        $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function __toString()
    {
        if ($this->isTrue()) {
            return 'TRUE';
        }

        return 'FALSE';
    }

    public function compareTo(Comparable $other)
    {
        if ($this->toNative() === $other->toNative()) {
            return 0;
        }

        if ($this->toNative() < $other->toNative()) {
            return -1;
        }

        return 1;
    }

    public function jsonSerialize()
    {
        return $this->isTrue();
    }

    public function isTrue()
    {
        return (bool) $this->value;
    }

    public function isFalse()
    {
        return $this->isTrue() ? false : true;
    }

    public function not()
    {
        if ($this->isTrue()) {
            return new static(false);
        }

        return new static(true);
    }
}
