<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use JsonSerializable;

abstract class PrimitiveValueObject implements JsonSerializable
{
    protected $value;

    public function toNative()
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }
}
