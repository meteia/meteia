<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class FileFragment
{
    /**
     * @var FilesystemPath
     */
    private $path;

    /**
     * @var Line[]
     */
    private $lines;

    public function __construct(FilesystemPath $path, array $lines)
    {
        $this->path = $path;
        $this->lines = $lines;
    }

    public function path(): FilesystemPath
    {
        return $this->path;
    }

    /**
     * @return Line[]
     */
    public function lines(): array
    {
        return $this->lines;
    }
}
