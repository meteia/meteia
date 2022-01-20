<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects\Primitives;

use Meteia\Yeso\ValueObjects\PrimitiveValueObject;

class StringLiteral extends PrimitiveValueObject
{
    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

    public function caseInsensitiveEquals(StringLiteral $string): bool
    {
        return strcasecmp((string) $this, (string) $string) === 0;
    }

    public function caseSensitiveEquals(StringLiteral $string): bool
    {
        return strcmp((string) $this, (string) $string) === 0;
    }

    public function __toString()
    {
        return $this->value;
    }
}
