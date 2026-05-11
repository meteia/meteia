<?php

declare(strict_types=1);

namespace Meteia\Application\Exceptions;

use RuntimeException;

final class UnknownCommandEndpoint extends RuntimeException
{
    public function __construct(string $commandClass)
    {
        parent::__construct(\sprintf('No CommandEndpoint registered for `%s`.', $commandClass));
    }
}
