<?php

declare(strict_types=1);

namespace Meteia\Files\Contracts;

use Meteia\Files\StoredFile;
use Meteia\ValueObjects\Identity\Resource;
use Meteia\ValueObjects\Identity\Uri;

interface Storage
{
    public function store(Resource $src, string $dest): StoredFile;

    public function exists(string $dest): bool;

    public function canonicalUri(string $dest): Uri;

    public function internalUri(string $dest): Uri;

    public function delete(string $dest): void;
}
