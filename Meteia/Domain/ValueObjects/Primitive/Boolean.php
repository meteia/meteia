<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use JsonSerializable;
use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class Boolean extends ImmutablePrimitiveValueObject implements Comparable, JsonSerializable
{
    public function __construct($value)
    {
        $this->value = \filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function compareTo(Comparable $other)
    {
        if ($this->toNative() === $other->toNative()) {
            return 0;
        } elseif ($this->toNative() < $other->toNative()) {
            return -1;
        } else {
            return 1;
        }
    }

    public function __toString()
    {
        if ($this->isTrue()) {
            return 'TRUE';
        }

        return 'FALSE';
    }

    public function isTrue()
    {
        return boolval($this->value);
    }

    public function isFalse()
    {
        return $this->isTrue() ? false : true;
    }

    public function Not()
    {
        if ($this->isTrue()) {
            return new Boolean(false);
        }

        return new Boolean(true);
    }

    public function jsonSerialize()
    {
        return $this->isTrue();
    }
}
