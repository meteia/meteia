<?php

declare(strict_types=1);

namespace Meteia\Yeso\Exceptions;

use Exception;

class ImproperType extends Exception
{
    public function __construct(string $type, array $allowed_types)
    {
        $message = sprintf('"%s" is not one of the valid types ("%s") for this value object.', $type, implode(', ', $allowed_types));
        parent::__construct($message);
    }
}
