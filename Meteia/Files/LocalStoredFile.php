<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Files\Contracts\StoredFile;
use Meteia\ValueObjects\Identity\Uri;

class LocalStoredFile implements StoredFile
{
    public function __construct(
        private readonly Uri $uri,
    ) {
    }

    public function uri(): Uri
    {
        return $this->uri;
    }
}