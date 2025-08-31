<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class Boolean extends ImmutablePrimitiveValueObject implements Comparable, \JsonSerializable
{
    public function __construct($value)
    {
        $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    #[\Override]
    public function __toString()
    {
        if ($this->isTrue()) {
            return 'TRUE';
        }

        return 'FALSE';
    }

    #[\Override]
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

    public function isTrue()
    {
        return (bool) $this->value;
    }

    public function isFalse()
    {
        return $this->isTrue() ? false : true;
    }

    public function Not()
    {
        if ($this->isTrue()) {
            return new self(false);
        }

        return new self(true);
    }

    #[\Override]
    public function jsonSerialize()
    {
        return $this->isTrue();
    }
}
