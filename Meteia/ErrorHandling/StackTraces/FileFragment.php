<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class FileFragment
{
    /**
     * @param array<int, Line> $lines
     */
    public function __construct(
        public readonly FilesystemPath $path,
        public readonly array $lines,
    ) {}
}
