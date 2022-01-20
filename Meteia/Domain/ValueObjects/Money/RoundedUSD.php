<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Money;

use Meteia\Domain\Contracts\Money\RoundedMoney;
use Meteia\Domain\ValueObjects\Primitive\StringLiteral;

class RoundedUSD extends StringLiteral implements RoundedMoney
{
    public function formatted(): string
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);

        return $formatter->format($this->value);
    }
}
