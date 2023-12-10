<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Errors;

class ValueObjectInvalid extends \InvalidArgumentException
{
    public function __construct($value, array $allowed_types)
    {
        $message = sprintf('"%s" is not one of the valid types ("%s") for this value object.', $value, implode(', ', $allowed_types));
        parent::__construct($message);
    }
}
