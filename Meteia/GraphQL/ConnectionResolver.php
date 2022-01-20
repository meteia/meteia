<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\GraphQL\Types\ConnectionField;

trait ConnectionResolver
{
    private bool $debug = false;

    protected function processedRows(array $rows, array $args): object
    {
        $cursorField = '__cursor';
        if (!$args || !count($args)) {
            throw new \Exception('A type that has ' . get_called_class() . ' as a field is likely not passing through default arguments.');
        }
        $hasNextPage = $this->hasNextPage(count($rows), $args);
        if (isset($args[ConnectionField::ARG_FIRST])) {
            $rows = array_slice($rows, 0, $args[ConnectionField::ARG_FIRST]);
        }

        $hasPreviousPage = $this->hasPreviousPage(count($rows), $args);
        if (isset($args[ConnectionField::ARG_LAST])) {
            if (count($rows) > $args[ConnectionField::ARG_LAST]) {
                // Remove the extra row we used to determine pagination
                array_pop($rows);
            }
            $rows = array_slice($rows, 0, $args[ConnectionField::ARG_LAST]);
            $rows = array_reverse($rows);
        }

        /** @var array $edges */
        $edges = array_map(function ($row) use ($cursorField) {
            return $this->asEdge($row, $cursorField);
        }, $rows);
        $firstEdge = $edges[0] ?? null;
        $lastEdge = end($edges);

        return (object) [
            'id' => base64_encode(hash('sha1', json_encode($args), true)),
            'edges' => $edges,
            'pageInfo' => [
                'startCursor' => $firstEdge->cursor ?? null,
                'endCursor' => $lastEdge->cursor ?? null,
                'hasNextPage' => $hasNextPage,
                'hasPreviousPage' => $hasPreviousPage,
            ],
        ];
    }

    protected function hasNextPage($count, $args)
    {
        if (isset($args[ConnectionField::ARG_FIRST])) {
            if ($count > $args[ConnectionField::ARG_FIRST]) {
                return true;
            }
        }

        if (isset($args[ConnectionField::ARG_BEFORE])) {
            // FIXME: Not strictly true, but true enough
            return true;
        }

        return false;
    }

    protected function hasPreviousPage($count, $args)
    {
        if (isset($args[ConnectionField::ARG_LAST])) {
            if ($count > $args[ConnectionField::ARG_LAST]) {
                return true;
            }
        }

        if (isset($args[ConnectionField::ARG_AFTER])) {
            // FIXME: Not strictly true, but true enough
            return true;
        }

        return false;
    }

    private function asEdge($row, $cursorField)
    {
        if (!isset($row->{$cursorField})) {
            throw new \ErrorException("Row is missing the required field: $cursorField");
        }

        if ($cursorField === 'cursor_value') {
            return (object) [
                'cursor' => $this->debug ? $row->{$cursorField} : base64_encode($row->{$cursorField}),
                'node' => $row,
            ];
        }

        // if (!isset($row->primaryKey)) {
        //    throw new \ErrorException('Row is missing the required field: primaryKey');
        // }

        return (object) [
            'cursor' => $row->{$cursorField},
            'node' => $row,
        ];
    }
}
