<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Errors;

use InvalidArgumentException;

class ValueObjectInvalid extends InvalidArgumentException
{
    /**
     * @param array<array-key, string> $allowed_types
     */
    public function __construct(mixed $value, array $allowed_types)
    {
        $message = sprintf(
            '"%s" is not one of the valid types ("%s") for this value object.',
            \is_scalar($value) || $value instanceof \Stringable ? (string) $value : get_debug_type($value),
            implode(', ', $allowed_types),
        );
        parent::__construct($message);
    }
}
