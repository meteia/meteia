<?php

declare(strict_types=1);

namespace Meteia\Domain\Exceptions;

use InvalidArgumentException;
use Meteia\Exceptions\Contracts\IdempotentException;

class ValidArrayTypeNotDefinedException extends InvalidArgumentException implements IdempotentException
{
}
