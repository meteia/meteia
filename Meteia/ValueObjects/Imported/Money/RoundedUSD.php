<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Money;

use Meteia\Yeso\Contracts\Money\RoundedMoney;

class RoundedUSD implements RoundedMoney
{
    /** @var string */
    private $amount;

    public function __construct($amount)
    {
        $this->amount = (string) $amount;
    }

    public function equalTo(RoundedUSD $amount): bool
    {
        return bccomp($this->amount, (string) $amount, 15) === 0;
    }

    public function __toString()
    {
        return $this->amount;
    }
}
