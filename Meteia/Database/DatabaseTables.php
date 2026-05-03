<?php

declare(strict_types=1);

namespace Meteia\Database;

use Aura\Sql\ExtendedPdoInterface;

interface DatabaseTables extends ExtendedPdoInterface
{
    public function delete(string $table, array $whereBindings): void;

    public function insert(string $table, array $bindings): void;

    public function prepareBindings(array $values): array;

    public function select(string $table, array $whereBindings): array;

    public function update(string $table, array $setBindings, array $whereBindings): void;

    public function upsert(string $table, array $setBindings, array $whereBindings): void;
}
