<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\GraphQL\Types\ConnectionField;
use Tuupola\Base62;
use function Meteia\Polyfills\without_prefix;

trait ConnectionResolver
{
    private bool $debug = false;
    private static ?Base62 $codec = null;

    protected function processedRows(array $rows, array $args, array $cursorColumns): object
    {
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
        $edges = array_map(function ($row) use ($cursorColumns) {
            return $this->asEdge($row, $cursorColumns);
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

    private function asEdge(object $row, array $cursorColumns): object
    {
        $cursorValues = [];
        foreach ($cursorColumns as $cursorColumn) {
            if (!isset($row->{$cursorColumn})) {
                throw new \ErrorException(sprintf('Row is missing the required field: %s', $cursorColumn));
            }
            $cursorValues[] = $row->{$cursorColumn};
        }

        return (object) [
            'cursor' => $this->encodeCursor($cursorValues),
            'node' => $row,
        ];
    }

    private function encodeCursor(array $cursorData): string
    {
        if (static::$codec === null) {
            static::$codec = new Base62();
        }

        return 'cur_' . static::$codec->encode(implode('|', $cursorData));
    }

    protected function decodeCursor(string $cursor): array
    {
        if (static::$codec === null) {
            static::$codec = new Base62();
        }
        $cursor = without_prefix($cursor, 'cur_');

        return explode('|', static::$codec->decode($cursor));
    }
}
