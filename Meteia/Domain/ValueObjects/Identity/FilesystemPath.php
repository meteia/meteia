<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects\Identity;

use Meteia\Domain\ValueObjects\Primitive\StringLiteral;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webmozart\PathUtil\Path;

class FilesystemPath extends StringLiteral
{
    public function __construct(...$paths)
    {
        $paths = array_map('strval', $paths);
        $value = Path::join(...$paths);
        parent::__construct($value);
    }

    public function join(...$paths): FilesystemPath
    {
        return new static($this->value, ...$paths);
    }

    public function exists(): bool
    {
        return file_exists((string) $this);
    }

    public function directory(): FilesystemPath
    {
        return new static(Path::getDirectory($this->value));
    }

    public function name(): string
    {
        return Path::getFilename($this->value);
    }

    public function nameWithoutExtension(): string
    {
        return Path::getFilenameWithoutExtension($this->value);
    }

    public function extension(): string
    {
        return Path::getExtension($this->value);
    }

    /**
     * via: https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2#gistcomment-2036828.
     */
    public function delete($keepRootFolder = false)
    {
        $folder = (string) $this;
        if (empty($folder) || !file_exists($folder)) {
            return true; // No such file/folder exists.
        } elseif (is_file($folder) || is_link($folder)) {
            return @unlink($folder); // Delete file/link.
        }

        $inner = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($inner, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileInfo) {
            $action = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$action($fileInfo->getRealPath())) {
                return false; // Abort due to the failure.
            }
        }

        return !$keepRootFolder ? @rmdir($folder) : true;
    }
}
