<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;

abstract class IntegerLiteral extends PrimitiveValueObject
{
    public const PRECISION = 20;

    public function __construct($value)
    {
        $value = \filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new ValueObjectInvalid($value, ['int']);
        }

        $this->value = $value;
    }

    public function equalTo($integer): bool
    {
        return $this->compareTo($integer) === 0;
    }

    public function compareTo($integer): int
    {
        return bccomp((string) $this, (string) $integer, self::PRECISION);
    }

    public function add($integer): self
    {
        return new static(bcadd((string) $this, (string) $integer, self::PRECISION));
    }

    public function asInteger(): int
    {
        return (int) $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
