<?php

declare(strict_types=1);

namespace Meteia\Database\Errors;

use Exception;

class FailedToUpdate extends Exception
{
    public function __construct(string $table, string $query = '', array $bindings = [])
    {
        parent::__construct(sprintf('Failed to update row in table %s', $table), 500);
    }
}
