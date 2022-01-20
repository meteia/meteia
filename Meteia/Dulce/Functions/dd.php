<?php

declare(strict_types=1);

use Meteia\Http\Responses\JsonResponse;
use function Meteia\Http\Functions\send;
use function Meteia\Polyfills\common_prefix_length;

if (!function_exists('dump_object_via_reflection')) {
    function dump_object_via_reflection($object)
    {
        if (!is_object($object)) {
            return $object;
        }
        if ($object instanceof JsonSerializable) {
            return dump_object_via_reflection($object->jsonSerialize());
        }
        $reflectionClass = new ReflectionClass($object);

        $properties = $reflectionClass->getProperties();

        $array = get_object_vars($object);
        // $array['__class'] = get_class($object);
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if (is_object($value)) {
                $array[$property->getName()] = dump_object_via_reflection($value);
            } else {
                $array[$property->getName()] = $value;
            }
        }

        return $array;
    }
}

if (!function_exists('jdd')) {
    function jdd()
    {
        $stackTrace = debug_backtrace();
        $stackTrace = array_slice(array_filter($stackTrace, function ($frame) {
            return isset($frame['file'], $frame['line']) && strpos($frame['file'], '/vendor/') === false;
        }), 0);
        $commonPrefix = common_prefix_length(array_column($stackTrace, 'file'));
        $stackTrace = array_map(function ($frame) use ($commonPrefix) {
            return substr($frame['file'], $commonPrefix) . ':' . $frame['line'];
        }, $stackTrace);

        $data = [
            'data' => array_map('dump_object_via_reflection', func_get_args()),
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
    function hdd()
    {
        $id = bin2hex(random_bytes(2));
        $stackTrace = debug_backtrace();
        $stackTrace = array_slice(array_filter($stackTrace, function ($frame) {
            return isset($frame['file'], $frame['line']) && preg_match('#/(vendor|DependencyInjection)/#', $frame['file']) === 0;
        }), 0);
        $fileNames = array_pluck($stackTrace, 'file');
        $prefixLength = common_prefix_length($fileNames);
        $stackTrace = array_map(function ($frame) use ($prefixLength) {
            return substr($frame['file'], $prefixLength) . ':' . $frame['line'];
        }, $stackTrace);
        $stackTrace = array_reverse($stackTrace);

        $data = array_map('dump_object_via_reflection', func_get_args());
        array_map(function ($idx, $data) use ($id) {
            header("X-Debug-$id-Data-$idx: " . (is_string($data) ? $data : json_encode($data)), false);
        }, array_keys($data), $data);

        array_map(function ($idx, $data) use ($id) {
            header("X-Debug-$id-Trace-$idx: " . $data, false);
        }, array_keys($stackTrace), $stackTrace);
    }
}
