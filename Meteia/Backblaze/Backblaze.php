<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Meteia\Backblaze\Api\Api;
use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\KeyId;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\StoredFile;

class Backblaze implements Storage
{
    public function __construct(
        private readonly Api $api,
        private readonly KeyId $keyId,
        private readonly ApplicationKey $applicationKey,
    ) {
    }

    public function store($src, string $dest): StoredFile
    {
        // // TODO: Should the bucket be determined by path, or by something else?
        // [$bucket, $path] = explode(DIRECTORY_SEPARATOR, $path, 2);
        //
        //
        // $authorizedAccount = $this->api->authorizeAccount($this->keyId, $this->applicationKey);
        // $authorizedAccount->upload($stream, $bucket, $path);
        //
        // return new StoredFile();
    }
}
