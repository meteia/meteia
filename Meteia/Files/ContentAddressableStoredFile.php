<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\ValueObjects\Identity\Uri;

class ContentAddressableStoredFile
{
    public function __construct(
        public readonly FileHash $fileHash,
        public readonly Uri $publicUri,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->publicUri;
    }
}
