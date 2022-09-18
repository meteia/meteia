<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Application\ApplicationPublicDir;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\Contracts\StoredFile;
use Meteia\Http\Host;

class LocalStorage implements Storage
{
    public function __construct(
        private readonly ApplicationPublicDir $applicationPublicDir,
        private readonly Host $host,
    ) {
    }


    public function store($src, string $dest, string $mimeType): StoredFile
    {
        assert(is_resource($src));
        rewind($src);

        $onDiskDest = $this->applicationPublicDir->join('files', $dest);
        $onDiskDest->writeStream($src);

        return new LocalStoredFile($this->host->withPath(implode('/', ['files', $dest])));
    }
}
