<?php

declare(strict_types=1);

namespace Meteia\Files;

use Meteia\Files\Contracts\Storage;
use Meteia\Files\Contracts\StoredFile;
use Psr\Http\Message\StreamInterface;

class ContentAddressableStorage
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function store(StreamInterface $stream): StoredFile
    {
        $path = sha1(random_bytes(32));

        return $this->storage->store($stream, $path);
    }
}
