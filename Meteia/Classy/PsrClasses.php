<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Generator;
use IteratorAggregate;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Stringable;

readonly class PsrClasses implements IteratorAggregate
{
    public function __construct(
        private FilesystemPath $baseDirectory,
        private string|Stringable $namespacePrefix,
        private array $regex,
    ) {
    }

    public function getIterator(): Generator
    {
        $searchRoot = $this->baseDirectory->join($this->namespacePrefix)->realpath();
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
