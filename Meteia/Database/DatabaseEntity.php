<?php

declare(strict_types=1);

namespace Meteia\Database;

trait DatabaseEntity
{
    private function performInsert(Database $database, string $table): void
    {
        $bindings = get_object_vars($this);
        if (count($bindings) === 0) {
            throw new \Exception('Missing where bindings for insert');
        }

        $columns = implode(', ', array_map(fn ($column) => "`$column`", array_keys($bindings)));
        $columnBindings = implode(', ', array_map(fn ($column) => ":$column", array_keys($bindings)));

        $query = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, $columns, $columnBindings);
        $bindings = $database->prepareBindings($bindings);
        $database->perform($query, $bindings);
    }
}
