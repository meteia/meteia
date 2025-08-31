<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Application\ApplicationPublicDir;
use Meteia\Files\Contracts\Storage;
use Meteia\Http\Host;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Meteia\ValueObjects\Identity\Resource;
use Meteia\ValueObjects\Identity\Uri;

class LocalStorage implements Storage
{
    public function __construct(
        private readonly ApplicationPublicDir $applicationPublicDir,
        private readonly Host $host,
    ) {}

    #[\Override]
    public function canonicalUri(string $dest): Uri
    {
        return $this->host->withPath(implode('/', ['files', $dest]));
    }

    #[\Override]
    public function exists(string $dest): bool
    {
        return $this->onDiskDest($dest)->exists();
    }

    #[\Override]
    public function store(Resource $src, string $dest): StoredFile
    {
        $onDiskDest = $this->onDiskDest($dest);
        $src->writeStream($onDiskDest);

        return new StoredFile($this->canonicalUri($dest));
    }

    #[\Override]
    public function delete(string $dest): void
    {
        // noop
    }

    private function onDiskDest(string $dest): FilesystemPath
    {
        return $this->applicationPublicDir->join('files', $dest);
    }
}
