<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\GraphQL\Types\ConnectionField;

abstract class TableConnectionResolver implements Resolver
{
    use ConnectionResolver;

    public function __construct(
        private ExtendedPdoInterface $db,
        private string $table,
        private array $cursorOver = ['id'],
    ) {
    }

    public function data(mixed $root, array $args, RequestContext $requestContext): mixed
    {
        $cursorSeparator = '|';
        $cursorColumns = implode(',', $this->cursorOver);

        $where = [];
        $bindings = [
            'limit' => $args[ConnectionField::ARG_FIRST] + 1,
        ];

        $cursor = $args[ConnectionField::ARG_AFTER] ?? $args[ConnectionField::ARG_BEFORE] ?? false;
        if ($cursor) {
            $cursorDirection = isset($args[ConnectionField::ARG_AFTER]) ? '>' : '<';

            $cursor = explode($cursorSeparator, $cursor, count($this->cursorOver));
            $placeholders = str_repeat('?,', count($cursor) - 1) . '?';
            $where[] = sprintf('(%s) %s (%s)', $cursorColumns, $cursorDirection, $placeholders);
            $bindings = array_merge($bindings, $cursor);
        }

        $whereString = '';
        if ($where) {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }

        $query = <<<SQL
            SELECT CONCAT_WS('{$cursorSeparator}', {$cursorColumns}) as `__cursor`, {$this->table}.*
            FROM $this->table
            $whereString
            LIMIT :limit
        SQL;
        $rows = $this->db->fetchObjects($query, $bindings);

        return $this->processedRows($rows, $args);
    }
}
