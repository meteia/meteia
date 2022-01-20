<?php

declare(strict_types=1);

namespace Meteia\Files\Contracts;

use Meteia\Files\StoredFile;
use Psr\Http\Message\StreamInterface;

interface Storage
{
    public function store(StreamInterface $stream, string $path): StoredFile;
}
