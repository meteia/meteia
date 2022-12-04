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
            implode(DIRECTORY_SEPARATOR, ['vendor']),
            implode(DIRECTORY_SEPARATOR, ['dulce', 'functions']),
            implode(DIRECTORY_SEPARATOR, ['Meteia', 'Dulce']),
        ];

        foreach ($paths as $path) {
            if (str_contains($frame['file'], $path)) {
                return false;
            }
        }

        return true;
    }
}
