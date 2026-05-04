<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

abstract class PrimitiveValueObject implements \JsonSerializable
{
    public function __construct(
        protected readonly mixed $value,
    ) {}

    public function toNative(): mixed
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }
}
