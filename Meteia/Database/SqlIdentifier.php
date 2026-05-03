<?php

declare(strict_types=1);

namespace Meteia\Database;

final readonly class SqlIdentifier
{
    public function __construct(
        private string $identifier,
    ) {}

    public function quoted(): string
    {
        if ($this->identifier === '') {
            throw new \InvalidArgumentException('Database identifiers must be non-empty strings');
        }

        return '`' . str_replace('`', '``', $this->identifier) . '`';
    }
}
