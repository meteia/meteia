<?php

declare(strict_types=1);

namespace Meteia\Http\Exceptions;

use RuntimeException;

final class MissingMessageScope extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Request has no MessageScope attribute; SeedMessageScope middleware must run first.');
    }
}
