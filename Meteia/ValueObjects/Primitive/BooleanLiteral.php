<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;

abstract class BooleanLiteral extends PrimitiveValueObject implements Comparable
{
    public function __construct($value)
    {
        parent::__construct(filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    public function __toString()
    {
        return $this->isTrue() ? 'TRUE' : 'FALSE';
    }

    #[Override]
    public function compareTo(Comparable $other): int
    {
        return (bool) $this->toNative() <=> (bool) $other->toNative();
    }

    #[Override]
    public function jsonSerialize(): bool
    {
        return $this->isTrue();
    }

    public function isTrue(): bool
    {
        return (bool) $this->toNative();
    }

    public function isFalse(): bool
    {
        return !$this->isTrue();
    }

    public function not(): static
    {
        return new static(!$this->isTrue());
    }
}
