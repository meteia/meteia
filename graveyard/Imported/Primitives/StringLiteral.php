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

    public function __toString()
    {
        return $this->value;
    }

    public function caseInsensitiveEquals(self $string): bool
    {
        return strcasecmp((string) $this, (string) $string) === 0;
    }

    public function caseSensitiveEquals(self $string): bool
    {
        return strcmp((string) $this, (string) $string) === 0;
    }
}
