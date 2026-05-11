<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Money;

use Meteia\ValueObjects\Primitive\FloatLiteral;
use Meteia\ValueObjects\ValueObject;
use NumberFormatter;
use Override;

class Money extends ValueObject
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

    public function getFormatted(): string
    {
        $formatted = $this->formatter->format($this->getValue()->toNative());
        \assert($formatted !== false);

        return $formatted;
    }

    public function asPreciseUsd()
    {
        return new PreciseUsd($this->value);
    }

    #[Override]
    public function jsonSerialize()
    {
        return [
            'value' => $this->getValue(),
            'currency' => $this->getCurrency(),
            'formatted' => $this->getFormatted(),
        ];
    }
}
