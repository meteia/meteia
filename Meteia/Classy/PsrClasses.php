<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Generator;
use IteratorAggregate;
use Meteia\ValueObjects\Identity\FilesystemPath;

class PsrClasses implements IteratorAggregate
{
    public function __construct(
        private readonly FilesystemPath $baseDirectory,
        private readonly string $namespacePrefix,
        private readonly array $regex,
    ) {
    }

    public function getIterator(): Generator
    {
        $searchRoot = $this->baseDirectory->join($this->namespacePrefix);
        foreach ($searchRoot->find(...$this->regex) as $file) {
            $file = new FilesystemPath($file);
            $withoutPrefix = (string) $file->withoutPrefix($searchRoot);
            $className = $this->fileToClassName($withoutPrefix);
            if (!class_exists($className)) {
                continue;
            }

            yield $className;
        }
    }

    private function fileToClassName($file): string
    {
        $className = str_replace('.php', '', $file);
        $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
        $className = trim($className, '\\');

        return trim($this->namespacePrefix . '\\' . $className, '\\');
    }
}
