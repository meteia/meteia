<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\Domain\Contracts\PrimitiveValueObject;

abstract class ImmutablePrimitiveValueObject implements PrimitiveValueObject
{
    protected $value;

    public function toNative()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->toNative();
    }
}
