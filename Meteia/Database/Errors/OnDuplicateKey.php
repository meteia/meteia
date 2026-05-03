<?php

declare(strict_types=1);

namespace Meteia\Database\Errors;

final class OnDuplicateKey extends \Exception
{
    public function __construct(string $table, string $_query = '', array $_bindings = [])
    {
        parent::__construct(sprintf('Duplicate key error during insert into table %s', $table), 500);
    }
}
