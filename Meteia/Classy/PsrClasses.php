<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Meteia\ValueObjects\Identity\FilesystemPath;

final readonly class PsrClasses implements Classes
{
    public function __construct(
        private FilesystemPath $baseDirectory,
        private string|\Stringable $namespacePrefix,
        private array $regex,
    ) {}

    #[\Override]
    public function getIterator(): \Generator
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

    private function fileToClassName(string $file): string
    {
        return $file
            |> (static fn(string $f): string => str_replace('.php', '', $f))
            |> (static fn(string $f): string => str_replace(\DIRECTORY_SEPARATOR, '\\', $f))
            |> (static fn(string $f): string => trim($f, '\\'))
            |> (fn(string $f): string => trim($this->namespacePrefix . '\\' . $f, '\\'));
    }
}
