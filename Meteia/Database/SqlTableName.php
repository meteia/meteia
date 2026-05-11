<?php

declare(strict_types=1);

namespace Meteia\Database;

use InvalidArgumentException;

final readonly class SqlTableName
{
    public function __construct(
        private string $table,
    ) {}

    public function quoted(): string
    {
        if ($this->table === '') {
            throw new InvalidArgumentException('Database table names must be non-empty strings');
        }

        return implode('.', array_map(
            static fn(string $part): string => new SqlIdentifier($part)->quoted(),
            explode('.', $this->table),
        ));
    }
}
