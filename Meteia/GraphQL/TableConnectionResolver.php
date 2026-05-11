<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\Database\DatabaseTables;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Types\ConnectionField;
use Override;

use function Meteia\Polyfills\array_map_assoc;

abstract class TableConnectionResolver implements Resolver, TableConnectionBindings
{
    use ConnectionResolver;

    /**
     * @param list<string> $cursorOver
     */
    public function __construct(
        private readonly DatabaseTables $db,
        private readonly string $table,
        private readonly array $cursorOver = ['id'],
    ) {}

    #[Override]
    public function data(mixed $root, array $args, RequestContext $requestContext): object
    {
        $cursor = $args[ConnectionField::ARG_AFTER] ?? $args[ConnectionField::ARG_BEFORE] ?? false;
        $cursorColumns = implode(',', $this->cursorOver);
        $cursorDirection = !$cursor || isset($args[ConnectionField::ARG_AFTER]) ? 'forward' : 'reverse';

        $where = [];
        $bindings = [
            'limit' => (int) $args[ConnectionField::ARG_FIRST] + 1,
        ];

        if ($cursor !== false) {
            $compare = $cursorDirection === 'forward' ? '>' : '<';

            $cursorValues = $this->decodeCursor((string) $cursor);
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
            $firstRow = $rows[0] ?? [];
            \assert(\is_array($firstRow));
            $columnNameMap = array_map_assoc(static fn($i, $column) => [
                (string) $column => lcfirst(implode('', array_map(ucfirst(...), explode('_', (string) $column)))),
            ], array_keys($firstRow));
            $rows = array_map(static function ($row) use ($columnNameMap) {
                \assert(\is_array($row));

                return (object) array_map_assoc(static function ($column, $value) use ($columnNameMap): array {
                    $key = $columnNameMap[(string) $column] ?? (string) $column;
                    \assert(\is_string($key));

                    return [$key => $value];
                }, $row);
            }, $rows);
        }

        /** @var array<int, object> $rowsForReturn */
        $rowsForReturn = array_values($rows);

        /** @var array<string, mixed> $args */
        return $this->processedRows($rowsForReturn, $args, array_values($this->cursorOver));
    }
}
