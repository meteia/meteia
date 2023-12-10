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
        $filteredValue = filter_var($value, FILTER_VALIDATE_INT);

        if ($filteredValue === false) {
            throw new ImproperType(\gettype($value), ['int']);
        }

        $this->value = $filteredValue;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

    public function equalTo(self $value): bool
    {
        return bccomp((string) $this, (string) $value, self::PRECISION) === 0;
    }

    public function add(self $value): self
    {
        return new self(bcadd((string) $this, (string) $value, self::PRECISION));
    }

    public function subtract(self $value): self
    {
        return new self(bcsub((string) $this, (string) $value, self::PRECISION));
    }

    public function divideBy(self $by): self
    {
        return new self(bcdiv((string) $this, (string) $by, self::PRECISION));
    }

    public function multiplyBy(self $by): self
    {
        return new self(bcmul((string) $this, (string) $by, self::PRECISION));
    }

    public function asFloat(): FloatLiteral
    {
        return new FloatLiteral($this->value);
    }
}
