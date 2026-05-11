<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use JsonSerializable;
use Meteia\Domain\Contracts\Comparable;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;
use Stringable;

class Boolean extends PrimitiveValueObject implements Comparable, JsonSerializable, Stringable
{
    public function __construct(mixed $value)
    {
        parent::__construct(filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }

    #[Override]
    public function __toString(): string
    {
        if ($this->isTrue()) {
            return 'TRUE';
        }

        return 'FALSE';
    }

    #[Override]
    public function compareTo(Comparable $other): int
    {
        $self = $this->isTrue();
        $otherBool = (bool) $other->toNative();
        if ($self === $otherBool) {
            return 0;
        }
        if ($self === false) {
            return -1;
        }

        return 1;
    }

    public function isTrue(): bool
    {
        return (bool) $this->value;
    }

    public function isFalse(): bool
    {
        return !$this->isTrue();
    }

    public function Not(): self
    {
        return new self(!$this->isTrue());
    }

    #[Override]
    public function jsonSerialize(): bool
    {
        return $this->isTrue();
    }
}
