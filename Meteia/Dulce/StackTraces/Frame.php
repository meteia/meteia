<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class Frame
{
    public function __construct(
        public readonly FilesystemPath $path,
        public readonly int $lineNumber,
        public readonly FileFragment $fileFragment,
    ) {
    }
}
