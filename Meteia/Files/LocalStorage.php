<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Application\ApplicationPublicDir;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\Contracts\StoredFile;
use Meteia\Http\Host;
use Meteia\ValueObjects\Identity\FilesystemPath;

class LocalStorage implements Storage
{
    public function __construct(
        private readonly ApplicationPublicDir $applicationPublicDir,
        private readonly Host $host,
    ) {
    }

    public function exists(string $dest): bool
    {
        return $this->onDiskDest($dest)->exists();
    }

    public function store($src, string $dest): StoredFile
    {
        assert(is_resource($src));
        rewind($src);

        $onDiskDest = $this->onDiskDest($dest);
        $onDiskDest->writeStream($src);

        return new LocalStoredFile($this->host->withPath(implode('/', ['files', $dest])));
    }

    private function onDiskDest(string $dest): FilesystemPath
    {
        return $this->applicationPublicDir->join('files', $dest);
    }
}
