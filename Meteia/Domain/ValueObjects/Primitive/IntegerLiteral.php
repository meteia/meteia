<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Primitive;

use Meteia\Domain\Exceptions\InvalidValueObjectException;
use Meteia\Domain\ValueObjects\ImmutablePrimitiveValueObject;

class IntegerLiteral extends ImmutablePrimitiveValueObject
{
    public const PRECISION = 12;

    public function __construct($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false) {
            throw new InvalidValueObjectException($value, ['int']);
        }

        $this->value = $value;
    }

    #[\Override]
    public function __toString()
    {
        return (string) $this->value;
    }

    public function compareTo($integer)
    {
        return bccomp((string) $this, (string) $integer, self::PRECISION);
    }

    public function equalTo($integer)
    {
        return $this->compareTo($integer) === 0;
    }

    public function add($integer)
    {
        return new self(bcadd((string) $this, (string) $integer, self::PRECISION));
    }

    public function asInteger()
    {
        return (int) $this->value;
    }
}
