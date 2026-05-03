<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\Database\Database;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Types\ConnectionField;

use function Meteia\Polyfills\array_map_assoc;

abstract class TableConnectionResolver implements Resolver, TableConnectionBindings
{
    use ConnectionResolver;

    public function __construct(
        private readonly Database $db,
        private readonly string $table,
        private readonly array $cursorOver = ['id'],
    ) {}

    #[\Override]
    public function data(mixed $root, array $args, RequestContext $requestContext): object
    {
        $cursor = $args[ConnectionField::ARG_AFTER] ?? $args[ConnectionField::ARG_BEFORE] ?? false;
        $cursorColumns = implode(',', $this->cursorOver);
        $cursorDirection = !$cursor || isset($args[ConnectionField::ARG_AFTER]) ? 'forward' : 'reverse';

        $where = [];
        $bindings = [
            'limit' => $args[ConnectionField::ARG_FIRST] + 1,
        ];

        if ($cursor) {
            $compare = $cursorDirection === 'forward' ? '>' : '<';

            $cursorValues = $this->decodeCursor($cursor);
            \assert(\count($cursorValues) === \count($this->cursorOver));
            $placeholders = str_repeat('?,', \count($cursorValues) - 1) . '?';
            $where[] = sprintf('(%s) %s (%s)', $cursorColumns, $compare, $placeholders);
            $bindings = array_merge($cursorValues, $bindings);
        }

        foreach ($this->resolveWhereBindings($root, $args, $requestContext) as $column => $value) {
            if ($value === null) {
                $where[] = sprintf('`%s` IS NULL', $column);

                continue;
            }
            $where[] = sprintf('`%s` = :%s', $column, $column);
            $bindings[$column] = $value;
        }

        $whereString = '';
        if (\count($where)) {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }

        // TODO: Add order-by args
        $orderString = sprintf('ORDER BY %s DESC', $cursorColumns);

        $query = sprintf('SELECT * FROM `%s` %s %s LIMIT :limit', $this->table, $whereString, $orderString);
        // jdd($query, $this->db->prepareBindings($bindings));
        $rows = $this->db->fetchAll($query, $this->db->prepareBindings($bindings));
        if ($cursorDirection === 'reverse') {
            $rows = array_reverse($rows);
        }
        if (\count($rows)) {
            // FIXME: This seems pretty expensive just to fix column names... would it better in the query?
            // Maybe we could ask for mappings (maybe even static?) similar to how we do resolveWhereBindings
            // TODO: This probably should be a trait, or some place more reusable
            $columnNameMap = array_map_assoc(static fn($i, $column) => [
                $column => lcfirst(implode('', array_map(ucfirst(...), explode('_', $column)))),
            ], array_keys($rows[0]));
            $rows = array_map(
                static fn($row) => (object) array_map_assoc(static fn($column, $row) => [
                    $columnNameMap[$column] => $row,
                ], $row),
                $rows,
            );
        }

        return $this->processedRows($rows, $args, $this->cursorOver);
    }
}
