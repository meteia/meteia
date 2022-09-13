<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\Database\Database;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Types\ConnectionField;

abstract class TableConnectionResolver implements Resolver
{
    use ConnectionResolver;

    public function __construct(
        private readonly Database $db,
        private readonly string $table,
        private readonly array $cursorOver = ['id'],
    ) {
    }

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
            assert(count($cursorValues) === count($this->cursorOver));
            $placeholders = str_repeat('?,', count($cursorValues) - 1) . '?';
            $where[] = sprintf('(%s) %s (%s)', $cursorColumns, $compare, $placeholders);
            $bindings = array_merge($cursorValues, $bindings);
        }

        $whereString = '';
        if ($where) {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }

        $query = sprintf('SELECT * FROM %s %s LIMIT :limit', $this->table, $whereString);
        $rows = $this->db->fetchObjects($query, $bindings);
        if ($cursorDirection === 'reverse') {
            $rows = array_reverse($rows);
        }

        return $this->processedRows($rows, $args, $this->cursorOver);
    }
}
