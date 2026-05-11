<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Money;

use Meteia\ValueObjects\Contracts\Money\RoundedMoney;
use Meteia\ValueObjects\Primitive\StringLiteral;

class RoundedUsd extends StringLiteral implements RoundedMoney
{
    public function formatted(): string
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);

        return $formatter->format($this->value);
    }
}
