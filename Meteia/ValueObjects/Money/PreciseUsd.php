<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Money;

use Meteia\ValueObjects\Contracts\Money\PreciseMoney;
use Override;
use Stringable;

class PreciseUsd implements PreciseMoney, Stringable
{
    public const PRECISION = 6;

    /** @var numeric-string */
    protected string $amount;

    public function __construct(string|float|int|Stringable $amount)
    {
        $value = (string) $amount;
        \assert(is_numeric($value));
        $this->amount = $value;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->amount;
    }

    #[Override]
    public function round(int $precision = 2): RoundedUsd
    {
        return new RoundedUsd(self::bcround($this->amount, $precision));
    }

    public function equalTo(string|float|int|Stringable $amount): bool
    {
        return bccomp($this->amount, self::numericString($amount), static::PRECISION) === 0;
    }

    public function lessThan(string|float|int|Stringable $amount): bool
    {
        return bccomp($this->amount, self::numericString($amount), static::PRECISION) === -1;
    }

    public function greaterThan(string|float|int|Stringable $amount): bool
    {
        return bccomp($this->amount, self::numericString($amount), static::PRECISION) === 1;
    }

    public function lessThanOrEq(string|float|int|Stringable $amount): bool
    {
        return bccomp($this->amount, self::numericString($amount), static::PRECISION) <= 0;
    }

    public function greaterThanOrEq(string|float|int|Stringable $amount): bool
    {
        return bccomp($this->amount, self::numericString($amount), static::PRECISION) >= 0;
    }

    public function add(string|float|int|Stringable $b): self
    {
        return new self(bcadd($this->amount, self::numericString($b), self::PRECISION));
    }

    public function subtract(string|float|int|Stringable $b): self
    {
        return new self(bcsub($this->amount, self::numericString($b), self::PRECISION));
    }

    public function multiply(string|float|int|Stringable $value): self
    {
        return new self(bcmul($this->amount, self::numericString($value), self::PRECISION));
    }

    public function divide(string|float|int|Stringable $value): self
    {
        return new self(bcdiv($this->amount, self::numericString($value), self::PRECISION));
    }

    public function abs(): self
    {
        $abs = ltrim($this->amount, '-');

        return new self($abs);
    }

    public function asCents(): int
    {
        return (int) bcmul(self::bcround($this->amount, 2), '100', self::PRECISION);
    }

    /**
     * @return numeric-string
     */
    private static function numericString(string|float|int|Stringable $amount): string
    {
        $value = (string) $amount;
        \assert(is_numeric($value));

        return $value;
    }

    /**
     * @param numeric-string $value
     *
     * @return numeric-string
     */
    private static function bcround(string $value, int $precision): string
    {
        $rounded = number_format((float) $value, $precision, '.', '');
        \assert(is_numeric($rounded));

        return $rounded;
    }
}
