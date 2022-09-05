<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

class FrameFilterMeteia implements FrameFilter
{
    public function filter(array $frame): bool
    {
        if (!isset($frame['file'])) {
            return false;
        }

        $paths = [
            join(DIRECTORY_SEPARATOR, ['vendor']),
            join(DIRECTORY_SEPARATOR, ['dulce', 'functions']),
            join(DIRECTORY_SEPARATOR, ['Meteia', 'Dulce']),
        ];

        foreach ($paths as $path) {
            if (str_contains($frame['file'], $path)) {
                return false;
            }
        }

        return true;
    }
}
