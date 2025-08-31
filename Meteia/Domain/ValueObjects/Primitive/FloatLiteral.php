<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use Meteia\Domain\Exceptions\InvalidValueObjectException;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class FloatLiteral extends ImmutablePrimitiveValueObject
{
    public const PRECISION = 12;

    public function __construct($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            throw new InvalidValueObjectException($value, ['float']);
        }

        $this->value = $value;
    }

    #[\Override]
    public function __toString()
    {
        return (string) $this->value;
    }

    public function add($amount)
    {
        return new static(bcadd($this, (string) $amount, static::PRECISION));
    }

    public function subtract($amount)
    {
        return new static(bcsub($this, (string) $amount, static::PRECISION));
    }

    public function multiply($by)
    {
        return new static(bcmul($this, (string) $by, static::PRECISION));
    }

    public function divide($by)
    {
        return new static(bcdiv($this, (string) $by, static::PRECISION));
    }

    public function greaterThanOrEq($other): bool
    {
        return bccomp($this, (string) $other, static::PRECISION) >= 0;
    }

    public function greaterThan($other): bool
    {
        return bccomp($this, (string) $other, static::PRECISION) === 1;
    }

    public function lessThan($other): bool
    {
        return bccomp($this, (string) $other, static::PRECISION) === 0;
    }

    public function lessThanOrEq($other): bool
    {
        return bccomp($this, (string) $other, static::PRECISION) <= 0;
    }
}
