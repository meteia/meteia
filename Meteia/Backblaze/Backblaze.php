<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Meteia\Backblaze\Api\Api;
use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\KeyId;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\StoredFile;
use Psr\Http\Message\StreamInterface;

class Backblaze implements Storage
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var KeyId
     */
    private $keyId;

    /**
     * @var ApplicationKey
     */
    private $applicationKey;

    public function __construct(Api $api, KeyId $keyId, ApplicationKey $applicationKey)
    {
        $this->api = $api;
        $this->keyId = $keyId;
        $this->applicationKey = $applicationKey;
    }

    public function store(StreamInterface $stream, string $path): StoredFile
    {
        // TODO: Should the bucket be determined by path, or by something else?
        [$bucket, $path] = explode(DIRECTORY_SEPARATOR, $path, 2);
        $path = implode(DIRECTORY_SEPARATOR, $path);

        $authorizedAccount = $this->api->authorizeAccount($this->keyId, $this->applicationKey);
        $authorizedAccount->upload($stream, $bucket, $path);

        return new StoredFile();
    }
}
