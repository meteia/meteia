<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Generator;
use IteratorAggregate;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Webmozart\Glob\Glob;

class PsrClasses implements IteratorAggregate
{
    public function __construct(
        private FilesystemPath $baseDirectory,
        private string $namespacePrefix,
        private array $globParts = ['**', '*.php'],
    ) {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function __toString(): string
    {
        return sprintf('path=%s/%s/%s', $this->baseDirectory, $this->namespacePrefix, implode(DIRECTORY_SEPARATOR, $this->globParts));
    }

    public function getIterator(): Generator
    {
        // Work around https://bugs.php.net/bug.php?id=72095
        if (!defined('GLOB_BRACE')) {
            define('GLOB_BRACE', 0);
        }

        $searchRoot = $this->baseDirectory->join($this->namespacePrefix);
        $glob = (string) $searchRoot->join(...$this->globParts);
        foreach (Glob::glob($glob) as $file) {
            $file = new FilesystemPath($file);
            $className = $this->fileToClassName((string) $file->withoutPrefix($searchRoot));
            if (!class_exists($className)) {
                continue;
            }

            yield $className;
        }
    }

    private function fileToClassName($file)
    {
        $className = str_replace('.php', '', $file);
        $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
        $className = trim($className, '\\');

        return trim($this->namespacePrefix . '\\' . $className, '\\');
    }
}
