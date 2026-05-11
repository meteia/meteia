<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use ErrorException;
use Exception;
use Meteia\GraphQL\Types\ConnectionField;
use Tuupola\Base62;

use function Meteia\Polyfills\without_prefix;

trait ConnectionResolver
{
    private bool $debug = false;
    private static ?Base62 $codec = null;

    /**
     * @param array<int, object> $rows
     * @param array<string, mixed> $args
     * @param list<string> $cursorColumns
     */
    protected function processedRows(array $rows, array $args, array $cursorColumns): object
    {
        if (!$args || !\count($args)) {
            throw new Exception(
                'A type that has ' . static::class . ' as a field is likely not passing through default arguments.',
            );
        }
        $hasNextPage = $this->hasNextPage(\count($rows), $args);
        if (isset($args[ConnectionField::ARG_FIRST])) {
            $rows = \array_slice($rows, 0, (int) $args[ConnectionField::ARG_FIRST]);
        }

        $hasPreviousPage = $this->hasPreviousPage(\count($rows), $args);
        if (isset($args[ConnectionField::ARG_LAST])) {
            if (\count($rows) > (int) $args[ConnectionField::ARG_LAST]) {
                array_pop($rows);
            }
            $rows = \array_slice($rows, 0, (int) $args[ConnectionField::ARG_LAST]);
            $rows = array_reverse($rows);
        }

        $edges = array_map(fn($row) => $this->asEdge($row, $cursorColumns), $rows);
        $firstEdge = $edges[0] ?? null;
        $lastEdge = end($edges);

        $encoded = json_encode($args);
        \assert($encoded !== false);

        return (object) [
            'id' => base64_encode(hash('sha1', $encoded, true)),
            'edges' => $edges,
            'pageInfo' => [
                'startCursor' => $firstEdge->cursor ?? null,
                'endCursor' => $lastEdge instanceof \stdClass ? $lastEdge->cursor ?? null : null,
                'hasNextPage' => $hasNextPage,
                'hasPreviousPage' => $hasPreviousPage,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $args
     */
    protected function hasNextPage(int $count, array $args): bool
    {
        if (isset($args[ConnectionField::ARG_FIRST])) {
            if ($count > (int) $args[ConnectionField::ARG_FIRST]) {
                return true;
            }
        }

        if (isset($args[ConnectionField::ARG_BEFORE])) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $args
     */
    protected function hasPreviousPage(int $count, array $args): bool
    {
        if (isset($args[ConnectionField::ARG_LAST])) {
            if ($count > (int) $args[ConnectionField::ARG_LAST]) {
                return true;
            }
        }

        if (isset($args[ConnectionField::ARG_AFTER])) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function decodeCursor(string $cursor): array
    {
        if (static::$codec === null) {
            static::$codec = new Base62();
        }
        $cursor = without_prefix($cursor, 'cur_');

        return explode('|', static::$codec->decode($cursor));
    }

    /**
     * @param list<string> $cursorColumns
     */
    private function asEdge(object $row, array $cursorColumns): object
    {
        $cursorValues = [];
        foreach ($cursorColumns as $cursorColumn) {
            if (!isset($row->{$cursorColumn})) {
                throw new ErrorException(sprintf('Row is missing the required field: %s', $cursorColumn));
            }
            $value = $row->{$cursorColumn};
            \assert(\is_scalar($value) || $value === null || $value instanceof \Stringable);
            $cursorValues[] = (string) $value;
        }

        return (object) [
            'cursor' => $this->encodeCursor($cursorValues),
            'node' => $row,
        ];
    }

    /**
     * @param list<string> $cursorData
     */
    private function encodeCursor(array $cursorData): string
    {
        if (static::$codec === null) {
            static::$codec = new Base62();
        }

        return 'cur_' . static::$codec->encode(implode('|', $cursorData));
    }
}
