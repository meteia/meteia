<?php

declare(strict_types=1);

namespace Meteia\Commands\Exceptions;

use RuntimeException;

final class UnknownCommandHandler extends RuntimeException
{
    public function __construct(string $commandClass)
    {
        parent::__construct(\sprintf('No CommandHandler registered for `%s`.', $commandClass));
    }
}
