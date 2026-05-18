<?php

declare(strict_types=1);

namespace Meteia\CommandLine;

final readonly class Dot
{
    /**
     * @param array<string, mixed> $data
     */
    public static function set(array &$data, string $path, mixed $value): void
    {
        $parts = explode('.', $path);
        $cur = &$data;
        foreach ($parts as $i => $part) {
            if ($i === \count($parts) - 1) {
                $cur[$part] = $value;
                continue;
            }
            if (!\array_key_exists($part, $cur)) {
                $cur[$part] = [];
            }

            if (!\is_array($cur[$part])) {
                $cur[$part] = [];
            }

            $cur = &$cur[$part];
        }
    }
}
