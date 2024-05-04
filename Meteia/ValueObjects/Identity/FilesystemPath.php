<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Meteia\Cryptography\Hash;
use Meteia\Cryptography\SecretKey;
use Meteia\ValueObjects\Primitive\StringLiteral;

class FilesystemPath extends StringLiteral
{
    public function __construct(...$paths)
    {
        $paths = array_map(static fn ($path) => rtrim((string) $path, \DIRECTORY_SEPARATOR), $paths);
        $value = implode(\DIRECTORY_SEPARATOR, $paths);

        parent::__construct($value);
    }

    public function delete(): void
    {
        unlink((string) $this);
    }

    public function exists(): bool
    {
        return file_exists((string) $this);
    }

    public function extension(): string
    {
        $filename = pathinfo((string) $this, PATHINFO_BASENAME);
        $extensionIdx = stripos($filename, '.');
        if ($extensionIdx === false) {
            return '';
        }

        return substr($filename, $extensionIdx);
    }

    public function find(string ...$regex): \Iterator
    {
        $basePath = $this->realpath();
        $regex = '#' . $basePath->join(...$regex) . '$#';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator((string) $basePath));

        return new \RegexIterator($iterator, $regex, \RegexIterator::MATCH);
    }

    public function hash(string $algo, ?SecretKey $hmacKey = null): Hash
    {
        $hashCtx = $hmacKey ? hash_init($algo, HASH_HMAC, (string) $hmacKey) : hash_init($algo);
        hash_update_file($hashCtx, (string) $this);

        return new Hash(hash_final($hashCtx));
    }

    public function isReadable(): bool
    {
        return is_readable((string) $this);
    }

    public function isDirectory(): bool
    {
        return is_dir((string) $this);
    }

    public function join(...$paths): self
    {
        return new self($this->value, ...$paths);
    }

    /**
     * @return \Iterator<int, string>
     */
    public function lines(int $start = 0, ?int $end = null): \Iterator
    {
        $file = new \SplFileObject((string) $this);
        $file->seek($start);
        $lineNumber = $start;
        while ($file->valid() && (!$end || $lineNumber <= $end)) {
            ++$lineNumber;

            yield $lineNumber => rtrim($file->getCurrentLine());
            $file->next();
        }
    }

    public function open(): Resource
    {
        $resource = fopen((string) $this, 'r');
        if ($resource === false) {
            throw new \Exception('Unable to open file: ' . $this);
        }

        return new Resource($resource);
    }

    public function read(): string
    {
        return file_get_contents((string) $this);
    }

    public function readJson(): mixed
    {
        return json_decode($this->read(), false, 512, JSON_THROW_ON_ERROR);
    }

    public function realpath(): static
    {
        return new static(realpath((string) $this));
    }

    public function withoutPrefix(self $prefix): self
    {
        return new self(trim(str_replace((string) $prefix, '', (string) $this), \DIRECTORY_SEPARATOR));
    }

    public function write(string $content): void
    {
        $dirname = \dirname((string) $this);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0o777, true);
        }
        $tmpName = tempnam($dirname, 'fsp-write');
        $success = file_put_contents($tmpName, $content);
        if ($success === false) {
            throw new \Exception('Failed to write file.');
        }
        rename($tmpName, (string) $this);
    }

    public function writeJson(array $array): void
    {
        $this->write(json_encode($array, JSON_THROW_ON_ERROR));
    }

    public function moveInto(self $destination): static
    {
        if (!$destination->exists()) {
            throw new \Exception('Destination does not exist.');
        }
        if (!$destination->isDirectory()) {
            throw new \Exception('Destination is not a directory.');
        }
        $destination = $destination->join($this->basename());
        rename((string) $this, (string) $destination);

        return $destination;
    }

    public function rename(self $newPath): static
    {
        $dirname = \dirname((string) $newPath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0o777, true);
        }
        $result = rename((string) $this, (string) $newPath);
        if (!$result) {
            throw new \Exception('Failed to rename file.');
        }

        return $newPath;
    }

    public function basename(): string
    {
        return pathinfo((string) $this, PATHINFO_BASENAME);
    }

    public function mimeType(): string
    {
        return mime_content_type((string) $this);
    }

    public function extensionFromMimeType(): string
    {
        $mimeType = $this->mimeType();

        return match ($mimeType) {
            'image/avif' => 'avif',
            'image/webp' => 'webp',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'video/mp4' => 'mp4',
            'video/ogg' => 'ogg',
            'video/webm' => 'webm',
            default => explode('+', explode('/', $mimeType, 2)[1], 2)[0],
        };
    }
}
