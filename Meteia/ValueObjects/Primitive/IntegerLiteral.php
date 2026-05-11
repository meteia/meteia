<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Meteia\ValueObjects\Errors\ValueObjectInvalid;
use Meteia\ValueObjects\PrimitiveValueObject;
use Override;
use Stringable;

abstract class IntegerLiteral extends PrimitiveValueObject implements Stringable
{
    public const PRECISION = 20;

    public function __construct(mixed $value)
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($filteredValue === false) {
            throw new ValueObjectInvalid($value, ['int']);
        }

        parent::__construct($filteredValue);
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->toNative();
    }

    public function equalTo(string|int|Stringable $integer): bool
    {
        return $this->compareTo($integer) === 0;
    }

    public function compareTo(string|int|Stringable $integer): int
    {
        return bccomp(self::numericString($this), self::numericString($integer), self::PRECISION);
    }

    public function add(string|int|Stringable $integer): static
    {
        return new static(bcadd(self::numericString($this), self::numericString($integer), self::PRECISION));
    }

    public function asInteger(): int
    {
        return (int) $this->toNative();
    }

    /**
     * @return numeric-string
     */
    private static function numericString(string|int|Stringable $value): string
    {
        $string = (string) $value;
        \assert(is_numeric($string));

        return $string;
    }
}
