<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use Meteia\Yeso\Exceptions\ImproperType;
use Meteia\Yeso\ValueObjects\PrimitiveValueObject;

class FloatLiteral extends PrimitiveValueObject
{
    public const PRECISION = 12;

    public function __construct($value)
    {
        $filteredValue = \filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($filteredValue === false) {
            throw new ImproperType(gettype($value), ['float']);
        }

        $this->value = $filteredValue;
    }

    public function __toString()
    {
        return strval($this->value);
    }

    public function add(FloatLiteral $value): FloatLiteral
    {
        return new FloatLiteral(bcadd((string) $this, (string) $value, self::PRECISION));
    }

    public function subtract(FloatLiteral $value): FloatLiteral
    {
        return new FloatLiteral(bcsub((string) $this, (string) $value, self::PRECISION));
    }

    public function multiplyBy(FloatLiteral $by): FloatLiteral
    {
        return new FloatLiteral(bcmul((string) $this, (string) $by, self::PRECISION));
    }

    public function divideBy(FloatLiteral $by): FloatLiteral
    {
        return new FloatLiteral(bcdiv((string) $this, (string) $by, self::PRECISION));
    }

    public function equalTo(FloatLiteral $value): bool
    {
        return bccomp((string) $this, (string) $value, self::PRECISION) === 0;
    }
}
