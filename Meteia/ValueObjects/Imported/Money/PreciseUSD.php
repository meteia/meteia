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

    public function equalTo(self $amount)
    {
        return bccomp($this->amount, (string) $amount, static::PRECISION) === 0;
    }

    public function add(self $amount)
    {
        return new self(bcadd($this->amount, (string) $amount, self::PRECISION));
    }

    public function subtract(self $amount)
    {
        return new self(bcsub($this->amount, (string) $amount, self::PRECISION));
    }

    public function multiplyBy($value)
    {
        return new self(bcmul($this->amount, (string) $value, self::PRECISION));
    }

    public function divideBy($value)
    {
        return new self(bcdiv($this->amount, (string) $value, self::PRECISION));
    }
}
