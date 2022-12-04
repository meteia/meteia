<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Money;

use Meteia\Domain\ValueObjects\ImmutableValueObject;
use Meteia\Domain\ValueObjects\Primitive\FloatLiteral;
use NumberFormatter;

class Money extends ImmutableValueObject
{
    protected FloatLiteral $value;

    protected Currency $currency;

    private NumberFormatter $formatter;

    public function __construct(FloatLiteral $value, Currency $currency)
    {
        $this->value = $value;
        $this->currency = $currency;
        $this->formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * @return FloatLiteral
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return float
     */
    public function getNativeValue()
    {
        return $this->value->toNative();
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getFormatted()
    {
        return $this->formatter->format($this->getValue()->toNative());
    }

    public function asPreciseUSD()
    {
        return new PreciseUSD($this->value);
    }

    public function jsonSerialize()
    {
        return [
            'value' => $this->getValue(),
            'currency' => $this->getCurrency(),
            'formatted' => $this->getFormatted(),
        ];
    }
}
