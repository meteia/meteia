<?php

declare(strict_types=1);

namespace Meteia\ObjectStorage;

use Meteia\Files\Contracts\StoredFile;
use Meteia\ValueObjects\Identity\Uri;

class ObjectStoredFile implements StoredFile
{
    public function __construct(
        private readonly Uri $uri,
    )
    {
    }


    public function uri(): Uri
    {
        return $this->uri;
    }
}
