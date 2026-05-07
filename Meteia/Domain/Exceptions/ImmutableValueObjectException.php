<?php

declare(strict_types=1);

namespace Meteia\Domain\Exceptions;

use Meteia\Exceptions\Contracts\IdempotentException;

class ImmutableValueObjectException extends \Exception implements IdempotentException
{
}
