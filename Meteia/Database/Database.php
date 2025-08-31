<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\ExtendedPdo;
use Meteia\Cryptography\Hash;
use Meteia\ValueObjects\Identity\UniqueId;

class Database extends ExtendedPdo implements MigrationDatabase
{
    public function delete(string $table, array $whereBindings): void
    {
        if (\count($whereBindings) === 0) {
            throw new \Exception('Missing where bindings for delete');
        }
        $whereColumn = implode(' AND ', array_map(
            static fn($column) => "`{$column}`=:{$column}",
            array_keys($whereBindings),
        ));
        $query = \sprintf('DELETE FROM %s WHERE %s', $this->quoteTableName($table), $whereColumn);
        $bindings = $this->prepareBindings($whereBindings);
        $this->perform($query, $bindings);
    }

    public function insert(string $table, array $bindings): void
    {
        if (\count($bindings) === 0) {
            throw new \Exception('Missing where bindings for insert');
        }

        $columns = implode(', ', array_map(static fn($column) => "`{$column}`", array_keys($bindings)));
        $columnBindings = implode(', ', array_map(static fn($column) => ":{$column}", array_keys($bindings)));

        $query = \sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->quoteTableName($table), $columns, $columnBindings);
        $bindings = $this->prepareBindings($bindings);
        $this->perform($query, $bindings);
    }

    public function prepareBindings(array $values): array
    {
        return array_map($this->prepareBoundValue(...), $values);
    }

    public function select(string $table, array $whereBindings): array
    {
        if (\count($whereBindings) === 0) {
            throw new \Exception('Missing where bindings for select');
        }

        $whereColumn = implode(' AND ', array_map(
            static fn($column) => "`{$column}` = :{$column}",
            array_keys($whereBindings),
        ));
        $query = \sprintf('SELECT * FROM %s WHERE %s', $this->quoteTableName($table), $whereColumn);
        $bindings = $this->prepareBindings($whereBindings);

        return $this->fetchObjects($query, $bindings);
    }

    public function update(string $table, array $setBindings, array $whereBindings): void
    {
        if (\count($setBindings) === 0 || \count($whereBindings) === 0) {
            throw new \Exception('Missing set and/or where bindings for update');
        }

        $setColumns = implode(', ', array_map(
            static fn($column) => "`{$column}`=:{$column}",
            array_keys($setBindings),
        ));
        $whereColumn = implode(' AND ', array_map(
            static fn($column) => "`{$column}`=:{$column}",
            array_keys($whereBindings),
        ));
        $query = \sprintf('UPDATE %s SET %s WHERE %s', $this->quoteTableName($table), $setColumns, $whereColumn);
        $bindings = $this->prepareBindings([
            ...$setBindings,
            ...$whereBindings,
        ]);
        $this->fetchAffected($query, $bindings);
    }

    public function upsert(string $table, array $setBindings, array $whereBindings): void
    {
        try {
            $this->insert($table, [...$setBindings, ...$whereBindings]);
        } catch (\PDOException $exception) {
            $setBindings = array_filter(
                $setBindings,
                static fn($key) => !\array_key_exists($key, $whereBindings),
                ARRAY_FILTER_USE_KEY,
            );
            if (\count($setBindings) === 0) {
                // Nothing to update
                return;
            }

            match ($exception->getCode()) {
                // 23000 = MySQL duplicate-key error
                '23000' => $this->update($table, $setBindings, $whereBindings),
                default => throw $exception,
            };
        }
    }

    private function prepareBoundValue(mixed $value): mixed
    {
        if (\is_array($value)) {
            return array_map($this->prepareBoundValue(...), $value);
        }
        if (!\is_object($value)) {
            return $value;
        }
        if ($value instanceof UniqueId) {
            return $value->bytes;
        }
        if ($value instanceof Hash) {
            return $value->binary();
        }
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if ($value instanceof \DateTime) {
            return $value->format(MySQL::DATE);
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format(MySQL::DATETIME);
        }
        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return $value;
    }

    private function quoteTableName(string $table): string
    {
        $parts = explode('.', $table, 2);
        array_map(static fn($part) => "`{$part}`", $parts);

        return implode('.', $parts);
    }
}
