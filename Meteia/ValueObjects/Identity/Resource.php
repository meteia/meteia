<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Cryptography\Hash;
use Meteia\Cryptography\SecretKey;

class Resource
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private readonly mixed $resource,
    ) {
        \assert(\is_resource($resource));
    }

    public function close(): void
    {
        fclose($this->resource);
    }

    public function contents(): string
    {
        rewind($this->resource);
        $contents = stream_get_contents($this->resource);
        \assert($contents !== false);

        return $contents;
    }

    public function hash(string $algo, ?SecretKey $hmacKey = null): Hash
    {
        rewind($this->resource);
        $hashCtx = $hmacKey ? hash_init($algo, HASH_HMAC, (string) $hmacKey) : hash_init($algo);
        hash_update_stream($hashCtx, $this->resource);

        return new Hash(hash_final($hashCtx));
    }

    public function resource(): mixed
    {
        return $this->resource;
    }

    public function size(): int
    {
        $stats = fstat($this->resource);
        if ($stats) {
            return $stats['size'];
        }
        rewind($this->resource);
        $content = stream_get_contents($this->resource);
        \assert($content !== false);

        return \strlen($content);
    }

    public function writeStream(FilesystemPath $destination): void
    {
        $dirname = \dirname((string) $destination);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0o777, true);
        }
        $dest = fopen((string) $destination, 'w');
        \assert($dest !== false);
        rewind($this->resource);
        stream_copy_to_stream($this->resource, $dest, -1);
        fclose($dest);
    }
}
