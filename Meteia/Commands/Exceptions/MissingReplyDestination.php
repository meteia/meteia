<?php

declare(strict_types=1);

namespace Meteia\Commands\Exceptions;

use RuntimeException;

final class MissingReplyDestination extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No reply destination is currently in flight.');
    }
}
