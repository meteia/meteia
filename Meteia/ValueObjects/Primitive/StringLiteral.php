<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Contracts\Comparable;
use Meteia\ValueObjects\Contracts\Text;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;

abstract class StringLiteral extends PrimitiveValueObject implements Text
{
    public function __construct($value)
    {
        parent::__construct((string) $value);
    }

    #[Override]
    public function toNative(): string
    {
        return (string) parent::toNative();
    }

    #[Override]
    public function __toString(): string
    {
        return $this->toNative();
    }

    #[Override]
    public function compareTo(Comparable $other): int
    {
        return strcasecmp($this->toNative(), (string) $other->toNative());
    }
}
