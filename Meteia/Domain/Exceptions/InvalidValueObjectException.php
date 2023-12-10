<?php

declare(strict_types=1);

namespace Meteia\Domain\Exceptions;

class InvalidValueObjectException extends \InvalidArgumentException
{
    public function __construct($value, array $allowed_types)
    {
        $this->message = sprintf('"%s" is not one of the valid types ("%s") for this value object.', $value, implode(', ', $allowed_types));
    }
}
