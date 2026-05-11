<?php

declare(strict_types=1);

namespace Meteia\Database;

use InvalidArgumentException;

final readonly class SqlBoundColumns
{
    public function __construct(
        private array $bindings,
        private string $placeholderPrefix,
    ) {}

    public function assignments(): string
    {
        return implode(', ', $this->mapped(fn(string $column, string $placeholder): string => \sprintf(
            '%s = :%s',
            $this->quoted($column),
            $placeholder,
        )));
    }

    public function bindings(): array
    {
        $bindings = [];
        $position = 0;
        foreach ($this->bindings as $value) {
            $bindings[$this->placeholder($position)] = $value;
            ++$position;
        }

        return $bindings;
    }

    public function columns(): string
    {
        return implode(', ', $this->mapped($this->quoted(...)));
    }

    public function comparisons(): string
    {
        return implode(' AND ', $this->mapped(fn(string $column, string $placeholder): string => \sprintf(
            '%s = :%s',
            $this->quoted($column),
            $placeholder,
        )));
    }

    public function placeholders(): string
    {
        return implode(
            ', ',
            $this->mapped(static fn(string $_column, string $placeholder): string => ':' . $placeholder),
        );
    }

    public function selfAssignment(): string
    {
        $column = array_key_first($this->bindings);
        if ($column === null) {
            throw new InvalidArgumentException('Cannot create self-assignment without columns');
        }

        $column = $this->quoted($this->column($column));

        return \sprintf('%s = %s', $column, $column);
    }

    private function mapped(callable $mapping): array
    {
        $mapped = [];
        $position = 0;
        foreach ($this->bindings as $column => $_value) {
            $mapped[] = $mapping($this->column($column), $this->placeholder($position));
            ++$position;
        }

        return $mapped;
    }

    private function column(int|string $column): string
    {
        if (!\is_string($column) || $column === '') {
            throw new InvalidArgumentException('Database column names must be non-empty strings');
        }

        return $column;
    }

    private function placeholder(int $position): string
    {
        return $this->placeholderPrefix . $position;
    }

    private function quoted(string $column): string
    {
        return new SqlIdentifier($column)->quoted();
    }
}
