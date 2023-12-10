<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

abstract class PrimitiveValueObject implements \JsonSerializable
{
    protected mixed $value;

    public function toNative(): mixed
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }
}
