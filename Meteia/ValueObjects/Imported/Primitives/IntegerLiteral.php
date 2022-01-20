<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use Meteia\Yeso\Exceptions\ImproperType;
use Meteia\Yeso\ValueObjects\PrimitiveValueObject;

class IntegerLiteral extends PrimitiveValueObject
{
    public const PRECISION = 0;

    public function __construct($value)
    {
        $filteredValue = \filter_var($value, FILTER_VALIDATE_INT);

        if ($filteredValue === false) {
            throw new ImproperType(gettype($value), ['int']);
        }

        $this->value = $filteredValue;
    }

    public function equalTo(IntegerLiteral $value): bool
    {
        return bccomp((string) $this, (string) $value, self::PRECISION) === 0;
    }

    public function add(IntegerLiteral $value): IntegerLiteral
    {
        return new IntegerLiteral(bcadd((string) $this, (string) $value, self::PRECISION));
    }

    public function subtract(IntegerLiteral $value): IntegerLiteral
    {
        return new IntegerLiteral(bcsub((string) $this, (string) $value, self::PRECISION));
    }

    public function divideBy(IntegerLiteral $by): IntegerLiteral
    {
        return new IntegerLiteral(bcdiv((string) $this, (string) $by, self::PRECISION));
    }

    public function multiplyBy(IntegerLiteral $by): IntegerLiteral
    {
        return new IntegerLiteral(bcmul((string) $this, (string) $by, self::PRECISION));
    }

    public function asFloat(): FloatLiteral
    {
        return new FloatLiteral($this->value);
    }

    public function __toString()
    {
        return strval($this->value);
    }
}
