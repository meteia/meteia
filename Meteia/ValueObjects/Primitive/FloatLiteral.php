<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\Domain\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;

abstract class FloatLiteral extends PrimitiveValueObject
{
    public const PRECISION = 12;

    public function __construct($value)
    {
        if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            throw new ValueObjectInvalid($value, ['float']);
        }

        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function add($amount)
    {
        return new static(bcadd($this, $amount, static::PRECISION));
    }

    public function subtract($amount)
    {
        return new static(bcsub($this, $amount, static::PRECISION));
    }

    public function multiply($by)
    {
        return new static(bcmul($this, $by, static::PRECISION));
    }

    public function divide($by)
    {
        return new static(bcdiv($this, $by, static::PRECISION));
    }
}
