<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class Frame
{
    /**
     * @var FilesystemPath
     */
    private $path;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var FileFragment
     */
    private $fileFragment;

    public function __construct(FilesystemPath $path, int $lineNumber, FileFragment $fileFragment)
    {
        $this->path = $path;
        $this->lineNumber = $lineNumber;
        $this->fileFragment = $fileFragment;
    }

    public function fileFragment(): FileFragment
    {
        return $this->fileFragment;
    }

    public function path(): FilesystemPath
    {
        return $this->path;
    }

    public function lineNumber(): int
    {
        return $this->lineNumber;
    }
}
