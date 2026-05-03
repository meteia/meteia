<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\ExtendedPdo;

final class Database extends ExtendedPdo implements DatabaseTables, MigrationDatabase
{
    #[\Override]
    public function delete(string $table, array $whereBindings): void
    {
        if (\count($whereBindings) === 0) {
            throw new \InvalidArgumentException('Missing where bindings for delete');
        }

        $where = new SqlBoundColumns($whereBindings, 'where');
        $query = \sprintf('DELETE FROM %s WHERE %s', $this->table($table), $where->comparisons());
        $this->perform($query, $this->prepareBindings($where->bindings()));
    }

    #[\Override]
    public function insert(string $table, array $bindings): void
    {
        if (\count($bindings) === 0) {
            throw new \InvalidArgumentException('Missing bindings for insert');
        }

        $insert = new SqlBoundColumns($bindings, 'insert');
        $query = \sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table($table),
            $insert->columns(),
            $insert->placeholders(),
        );
        $this->perform($query, $this->prepareBindings($insert->bindings()));
    }

    #[\Override]
    public function prepareBindings(array $values): array
    {
        return array_map(static fn(mixed $value): mixed => new SqlValue($value)->bound(), $values);
    }

    #[\Override]
    public function select(string $table, array $whereBindings): array
    {
        if (\count($whereBindings) === 0) {
            throw new \InvalidArgumentException('Missing where bindings for select');
        }

        $where = new SqlBoundColumns($whereBindings, 'where');
        $query = \sprintf('SELECT * FROM %s WHERE %s', $this->table($table), $where->comparisons());

        return $this->fetchObjects($query, $this->prepareBindings($where->bindings()));
    }

    #[\Override]
    public function update(string $table, array $setBindings, array $whereBindings): void
    {
        if (\count($setBindings) === 0 || \count($whereBindings) === 0) {
            throw new \InvalidArgumentException('Missing set and/or where bindings for update');
        }

        $set = new SqlBoundColumns($setBindings, 'set');
        $where = new SqlBoundColumns($whereBindings, 'where');
        $query = \sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->table($table),
            $set->assignments(),
            $where->comparisons(),
        );
        $this->fetchAffected($query, $this->prepareBindings([...$set->bindings(), ...$where->bindings()]));
    }

    #[\Override]
    public function upsert(string $table, array $setBindings, array $whereBindings): void
    {
        if (\count($setBindings) === 0 && \count($whereBindings) === 0) {
            throw new \InvalidArgumentException('Missing bindings for upsert');
        }

        $insertBindings = [...$setBindings, ...$whereBindings];
        $insert = new SqlBoundColumns($insertBindings, 'insert');
        $setBindings = array_filter(
            $setBindings,
            static fn(int|string $key): bool => !\array_key_exists($key, $whereBindings),
            ARRAY_FILTER_USE_KEY,
        );

        if (\count($setBindings) === 0) {
            $query = \sprintf(
                'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
                $this->table($table),
                $insert->columns(),
                $insert->placeholders(),
                $insert->selfAssignment(),
            );
            $this->perform($query, $this->prepareBindings($insert->bindings()));

            return;
        }

        $update = new SqlBoundColumns($setBindings, 'update');
        $query = \sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->table($table),
            $insert->columns(),
            $insert->placeholders(),
            $update->assignments(),
        );
        $this->perform($query, $this->prepareBindings([...$insert->bindings(), ...$update->bindings()]));
    }

    private function table(string $table): string
    {
        return new SqlTableName($table)->quoted();
    }
}
