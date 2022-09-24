<?php

declare(strict_types=1);

namespace Meteia\Files\Contracts;

interface Storage
{
    public function store($src, string $dest): StoredFile;

    public function exists(string $dest): bool;
}
