<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Money;

use BCMathExtended\BC;
use Meteia\Yeso\Contracts\Money\PreciseMoney;
use Meteia\Yeso\Contracts\Money\RoundedMoney;

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

    public function round(int $precision = 2): RoundedMoney
    {
        return new RoundedUSD(BC::round($this->amount, $precision));
    }

    public function equalTo(PreciseUSD $amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) === 0;
    }

    public function add(PreciseUSD $amount)
    {
        return new PreciseUSD(bcadd($this->amount, (string) $amount, PreciseUSD::PRECISION));
    }

    public function subtract(PreciseUSD $amount)
    {
        return new PreciseUSD(bcsub($this->amount, (string) $amount, PreciseUSD::PRECISION));
    }

    public function multiplyBy($value)
    {
        return new PreciseUSD(bcmul($this->amount, (string) $value, PreciseUSD::PRECISION));
    }

    public function divideBy($value)
    {
        return new PreciseUSD(bcdiv($this->amount, (string) $value, PreciseUSD::PRECISION));
    }
}
