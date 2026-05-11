<?php

declare(strict_types=1);

namespace Meteia\Database\Errors;

use Exception;

final class FailedToUpdate extends Exception
{
    public function __construct(string $table, string $_query = '', array $_bindings = [])
    {
        parent::__construct(sprintf('Failed to update row in table %s', $table), 500);
    }
}
