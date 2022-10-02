<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Cryptography\Hash;
use Meteia\Files\Configuration\ContentAddressableStorageSecretKey;
use Meteia\Files\Contracts\Storage;
use Meteia\ValueObjects\Identity\Resource;
use Meteia\ValueObjects\Identity\Uri;

class ContentAddressableStorage
{
    public function __construct(
        private readonly Storage $storage,
        private readonly ContentAddressableStorageSecretKey $contentAddressableStorageSecretKey,
    ) {
    }

    public function canonicalUri(Hash $hash, string $fileExtension): Uri
    {
        return $this->storage->canonicalUri($this->dest($hash, $fileExtension));
    }

    public function store(Resource $src, string $fileExtension): ContentAddressableStoredFile
    {
        $hash = $this->hash($src);
        $storedFile = $this->storage->store($src, $this->dest($hash, $fileExtension));

        return new ContentAddressableStoredFile(FileHash::fromHash($hash), $storedFile->publicUri);
    }

    public function hash(Resource $src): Hash
    {
        return $src->hash('sha256', $this->contentAddressableStorageSecretKey);
    }

    private function dest(Hash $fileHash, string $fileExtension): string
    {
        $hashString = $fileHash->base62();
        $fileExtension = trim(trim($fileExtension), '.');
        if (strlen($fileExtension)) {
            $fileExtension = '.' . $fileExtension;
        }

        return sprintf('%s/%s%s', substr($hashString, 0, 2), substr($hashString, 2), $fileExtension);
    }
}
