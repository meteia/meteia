<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\ValueObjects\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;

abstract class FloatLiteral extends PrimitiveValueObject
{
    public const PRECISION = 12;

    public function __construct($value)
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($filteredValue === false) {
            throw new ValueObjectInvalid($value, ['float']);
        }

        parent::__construct($filteredValue);
    }

    public function __toString()
    {
        return (string) $this->toNative();
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
