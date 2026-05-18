<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use ErrorException;
use Exception;
use Meteia\GraphQL\Types\ConnectionField;
use stdClass;
use Stringable;
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
        if ($args === []) {
            throw new Exception(
                'A type that has ' . static::class . ' as a field is likely not passing through default arguments.',
            );
        }
        $hasNextPage = $this->hasNextPage(\count($rows), $args);
        $firstArgument = $args[ConnectionField::ARG_FIRST] ?? null;
        if ($firstArgument !== null) {
            $first = (int) $firstArgument;
            $rows = \array_slice($rows, 0, $first);
        }

        $hasPreviousPage = $this->hasPreviousPage(\count($rows), $args);
        $lastArgument = $args[ConnectionField::ARG_LAST] ?? null;
        if ($lastArgument !== null) {
            $last = (int) $lastArgument;
            if (\count($rows) > $last) {
                array_pop($rows);
            }
            $rows = \array_slice($rows, 0, $last);
            $rows = array_reverse($rows);
        }

        $edges = array_map(fn($row) => $this->asEdge($row, $cursorColumns), $rows);
        $firstEdge = $edges[0] ?? null;
        $lastEdge = end($edges);

        $encoded = json_encode($args);
        \assert($encoded !== false, 'Connection arguments must encode to JSON.');

        return (object) [
            'id' => base64_encode(hash('sha1', $encoded, true)),
            'edges' => $edges,
            'pageInfo' => [
                'startCursor' => $firstEdge->cursor ?? null,
                'endCursor' => $lastEdge instanceof stdClass ? $lastEdge->cursor ?? null : null,
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
        $firstArgument = $args[ConnectionField::ARG_FIRST] ?? null;
        if ($firstArgument !== null) {
            if ($count > (int) $firstArgument) {
                return true;
            }
        }

        if (($args[ConnectionField::ARG_BEFORE] ?? null) !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $args
     */
    protected function hasPreviousPage(int $count, array $args): bool
    {
        $lastArgument = $args[ConnectionField::ARG_LAST] ?? null;
        if ($lastArgument !== null) {
            if ($count > (int) $lastArgument) {
                return true;
            }
        }

        if (($args[ConnectionField::ARG_AFTER] ?? null) !== null) {
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
            if (!property_exists($row, $cursorColumn)) {
                throw new ErrorException(sprintf('Row is missing the required field: %s', $cursorColumn));
            }
            $value = $row->{$cursorColumn};
            \assert(\is_scalar($value) || $value === null || $value instanceof Stringable, 'Cursor fields must be scalar values.');
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
