<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Money;

use BCMathExtended\BC;
use Meteia\Domain\Contracts\Money\PreciseMoney;
use Meteia\Domain\ValueObjects\Primitive\FloatLiteral;

class PreciseUSD implements PreciseMoney
{
    public const PRECISION = 6;

    /** @var string */
    protected $amount;

    public function __construct($amount)
    {
        $this->amount = (string) $amount;
    }

    public function __toString()
    {
        return (string) $this->amount;
    }

    public function round($precision = 2)
    {
        return new RoundedUSD(BC::round($this->amount, $precision));
    }

    public function equalTo($amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) === 0;
    }

    public function lessThan($amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) === -1;
    }

    public function greaterThan($amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) === 1;
    }

    public function lessThanOrEq($amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) <= 0;
    }

    public function greaterThanOrEq($amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) >= 0;
    }

    public function add($b)
    {
        return new PreciseUSD(bcadd($this->amount, (string) $b, PreciseUSD::PRECISION));
    }

    public function subtract($b)
    {
        return new PreciseUSD(bcsub($this->amount, (string) $b, PreciseUSD::PRECISION));
    }

    public function multiply($value)
    {
        return new PreciseUSD(bcmul($this->amount, (string) $value, PreciseUSD::PRECISION));
    }

    public function divide($value)
    {
        return new PreciseUSD(bcdiv($this->amount, (string) $value, PreciseUSD::PRECISION));
    }

    public function abs()
    {
        return new PreciseUSD(BC::abs($this->amount));
    }

    public function asCents(): int
    {
        return bcmul(BC::round($this->amount, 2), 100, PreciseUSD::PRECISION);
    }

    /**
     * @deprecated Use PreciseUSD and RoundedUSD whenever possible
     */
    public function asMoney(): Money
    {
        return new Money(new FloatLiteral($this->amount), new Currency('USD'));
    }
}
