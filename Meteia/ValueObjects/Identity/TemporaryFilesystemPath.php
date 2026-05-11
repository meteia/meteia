<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Psr\Http\Message\StreamInterface;

class TemporaryFilesystemPath extends FilesystemPath
{
    public function __destruct()
    {
        $path = (string) $this;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public static function forData(string $data): self
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'meteia');
        \assert($tempPath !== false);
        file_put_contents($tempPath, $data);

        return new self($tempPath);
    }

    public static function fromStream(StreamInterface $stream): self
    {
        $detached = $stream->detach();
        \assert($detached !== null);
        $resource = new Resource($detached);

        $tempPath = tempnam(sys_get_temp_dir(), 'meteia');
        \assert($tempPath !== false);
        $self = new self($tempPath);

        $resource->writeStream($self);

        return $self;
    }
}
