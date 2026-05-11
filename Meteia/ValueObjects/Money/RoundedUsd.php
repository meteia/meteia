<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Money;

use Meteia\ValueObjects\Contracts\Money\RoundedMoney;
use Meteia\ValueObjects\Primitive\StringLiteral;
use NumberFormatter;

class RoundedUsd extends StringLiteral implements RoundedMoney
{
    public function formatted(): string
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $value = (float) $this->toNative();
        $formatted = $formatter->format($value);
        \assert($formatted !== false);

        return $formatted;
    }
}
