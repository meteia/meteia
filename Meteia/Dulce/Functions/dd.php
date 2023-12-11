<?php

declare(strict_types=1);

use Meteia\Http\Responses\JsonResponse;

use function Meteia\Http\Functions\send;
use function Meteia\Polyfills\common_prefix_length;

if (!function_exists('dump_value')) {
    function dump_value(mixed $value, array &$seen = []): mixed
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::RFC3339);
            }
            $objectHash = spl_object_hash($value);
            if (isset($seen[$objectHash])) {
                return '[object ' . $value::class . ' ' . $objectHash . ']';
            }
            $seen[$objectHash] = true;

            return dump_value(get_object_vars($value), $seen);
        }
        if (is_array($value)) {
            return array_map(static fn ($innerValue) => dump_value($innerValue, $seen), $value);
        }
        if (is_string($value) && $value !== '' && !ctype_print($value)) {
            return '0x' . bin2hex($value);
        }

        return $value;
    }
}

if (!function_exists('jdd')) {
    function jdd(): never
    {
        $stackTrace = debug_backtrace();
        $stackTrace = array_slice(
            array_filter(
                $stackTrace,
                static fn ($frame) => isset($frame['file'], $frame['line']) && !str_contains($frame['file'], '/vendor/'),
            ),
            0,
        );
        $commonPrefix = common_prefix_length(array_column($stackTrace, 'file'));
        $stackTrace = array_map(
            static fn ($frame) => substr($frame['file'], $commonPrefix) . ':' . $frame['line'],
            $stackTrace,
        );

        $data = [
            'data' => array_map(dump_value(...), func_get_args()),
            'stackTrace' => $stackTrace,
        ];

        while (ob_get_level()) {
            ob_get_clean();
        }
        $response = new JsonResponse($data, 500, encodingOptions: JsonResponse::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT);

        send($response);

        exit;
    }
}

if (!function_exists('hdd')) {
    function hdd(): void
    {
        $id = bin2hex(random_bytes(2));
        $stackTrace = debug_backtrace();
        $stackTrace = array_slice(
            array_filter(
                $stackTrace,
                static fn ($frame) => isset($frame['file'], $frame['line'])
                    && preg_match('#/(vendor|DependencyInjection)/#', $frame['file']) === 0,
            ),
            0,
        );
        $fileNames = array_column($stackTrace, 'file');
        $prefixLength = common_prefix_length($fileNames);
        $stackTrace = array_map(
            static fn ($frame) => substr($frame['file'], $prefixLength) . ':' . $frame['line'],
            $stackTrace,
        );
        $stackTrace = array_reverse($stackTrace);

        $data = array_map(dump_value(...), func_get_args());
        array_map(
            static function ($idx, $data) use ($id): void {
                header("X-Debug-{$id}-Data-{$idx}: " . (is_string($data) ? $data : json_encode($data)), false);
            },
            array_keys($data),
            $data,
        );

        array_map(
            static function ($idx, $data) use ($id): void {
                header("X-Debug-{$id}-Trace-{$idx}: " . $data, false);
            },
            array_keys($stackTrace),
            $stackTrace,
        );
    }
}
