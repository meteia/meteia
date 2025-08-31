<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Meteia\ValueObjects\Identity\FilesystemPath;

class Definitions
{
    public static function glob(FilesystemPath $filesystemPath): array
    {
        $definitions = [];
        foreach (glob((string) $filesystemPath) as $filename) {
            $loaded = include $filename;
            if (!is_array($loaded)) {
                throw new \Exception("{$filename} should return an array");
            }
            $definitions[] = $loaded;
        }

        return array_merge([], ...$definitions);
    }
}
