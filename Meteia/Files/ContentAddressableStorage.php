<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Files\Contracts\Storage;
use Meteia\Files\Contracts\StoredFile;
use Tuupola\Base62;

class ContentAddressableStorage
{
    public function __construct(
        private readonly Storage $storage,
        private readonly ContentAddressableStorageSecretKey $contentAddressableStorageSecretKey,
        private readonly Base62 $base62,
    ) {
    }

    /**
     * @param resource $source
     */
    public function store($source, string $fileExtension, string $mimeType): StoredFile
    {
        assert(is_resource($source));
        rewind($source);

        if (strlen($fileExtension) && $fileExtension[0] !== '.') {
            $fileExtension = '.' . $fileExtension;
        }

        $hashCtx = hash_init('sha256', HASH_HMAC, (string) $this->contentAddressableStorageSecretKey);
        hash_update_stream($hashCtx, $source);
        $hash = $this->base62->encode(hash_final($hashCtx, true));
        $dest = sprintf('%s/%s%s', substr($hash, 0, 2), substr($hash, 2), $fileExtension);

        return $this->storage->store($source, $dest, $mimeType);
    }
}
