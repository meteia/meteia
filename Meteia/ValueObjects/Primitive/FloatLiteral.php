<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\ValueObjects\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;
use Stringable;

abstract class FloatLiteral extends PrimitiveValueObject implements Stringable
{
    public const PRECISION = 12;

    public function __construct(mixed $value)
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($filteredValue === false) {
            throw new ValueObjectInvalid($value, ['float']);
        }

        parent::__construct($filteredValue);
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->toNative();
    }

    #[Override]
    public function toNative(): float
    {
        return (float) parent::toNative();
    }

    public function add(string|float|int|\Stringable $amount): static
    {
        return new static(bcadd(self::numericString($this), self::numericString($amount), static::PRECISION));
    }

    public function subtract(string|float|int|\Stringable $amount): static
    {
        return new static(bcsub(self::numericString($this), self::numericString($amount), static::PRECISION));
    }

    public function multiply(string|float|int|\Stringable $by): static
    {
        return new static(bcmul(self::numericString($this), self::numericString($by), static::PRECISION));
    }

    public function divide(string|float|int|\Stringable $by): static
    {
        return new static(bcdiv(self::numericString($this), self::numericString($by), static::PRECISION));
    }

    /**
     * @return numeric-string
     */
    private static function numericString(string|float|int|\Stringable $value): string
    {
        $string = (string) $value;
        \assert(is_numeric($string));

        return $string;
    }
}
