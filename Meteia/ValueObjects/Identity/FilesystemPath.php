<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Iterator;
use Meteia\ValueObjects\Primitive\StringLiteral;
use SplFileObject;

class FilesystemPath extends StringLiteral
{
    public function __construct(...$paths)
    {
        $paths = array_map(fn ($path) => rtrim(strval($path), DIRECTORY_SEPARATOR), $paths);
        $value = implode(DIRECTORY_SEPARATOR, $paths);

        parent::__construct($value);
    }

    public function exists(): bool
    {
        return file_exists((string) $this);
    }

    public function getContents(): string
    {
        return file_get_contents((string) $this);
    }

    public function isReadable(): bool
    {
        return is_readable((string) $this);
    }

    public function join(...$paths): self
    {
        return new static($this->value, ...$paths);
    }

    /**
     * @return Iterator<int, string>
     */
    public function lines(int $start = 0, int $end = null): Iterator
    {
        $file = new SplFileObject((string) $this);
        $file->seek($start);
        while ($file->valid() && (!$end || $file->key() <= $end)) {
            yield $file->key() => rtrim($file->getCurrentLine());
            $file->next();
        }
    }

    public function realpath(): self
    {
        return new static(realpath((string) $this));
    }

    public function withoutPrefix(FilesystemPath $prefix): self
    {
        return new static(trim(str_replace((string) $prefix, '', (string) $this), '\\'));
    }
}
