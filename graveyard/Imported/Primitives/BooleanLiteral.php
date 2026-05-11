<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use Meteia\Yeso\ValueObjects\PrimitiveValueObject;

class BooleanLiteral extends PrimitiveValueObject
{
    public function __construct($value)
    {
        $this->value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function isTrue(): bool
    {
        return $this->value;
    }

    public function isFalse(): bool
    {
        return !$this->value;
    }

    public function not(): self
    {
        return new self(!$this->value);
    }
}
