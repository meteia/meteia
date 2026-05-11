<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use Exception;
use Iterator;
use Meteia\Cryptography\Hash;
use Meteia\Cryptography\SecretKey;
use Meteia\ValueObjects\Contracts\Path;
use Meteia\ValueObjects\Primitive\StringLiteral;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileObject;

class FilesystemPath extends StringLiteral implements Path
{
    public function __construct(...$paths)
    {
        $paths = array_map(static fn($path) => rtrim((string) $path, \DIRECTORY_SEPARATOR), $paths);
        $value = implode(\DIRECTORY_SEPARATOR, $paths);

        parent::__construct($value);
    }

    #[Override]
    public function delete(): void
    {
        unlink((string) $this);
    }

    #[Override]
    public function exists(): bool
    {
        return file_exists((string) $this);
    }

    #[Override]
    public function extension(): string
    {
        $filename = pathinfo((string) $this, PATHINFO_BASENAME);
        $extensionIdx = stripos($filename, '.');
        if (!$extensionIdx) {
            return '';
        }

        return substr($filename, $extensionIdx);
    }

    #[Override]
    public function find(string ...$regex): Iterator
    {
        $basePath = $this->realpath();
        $regex = '#' . $basePath->join(...$regex) . '$#';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator((string) $basePath));

        return new RegexIterator($iterator, $regex, RegexIterator::MATCH);
    }

    #[Override]
    public function hash(string $algo, ?SecretKey $hmacKey = null): Hash
    {
        $hashCtx = $hmacKey ? hash_init($algo, HASH_HMAC, (string) $hmacKey) : hash_init($algo);
        hash_update_file($hashCtx, (string) $this);

        return new Hash(hash_final($hashCtx));
    }

    #[Override]
    public function isReadable(): bool
    {
        return is_readable((string) $this);
    }

    #[Override]
    public function isDirectory(): bool
    {
        return is_dir((string) $this);
    }

    #[Override]
    public function join(...$paths): self
    {
        return new self((string) $this, ...$paths);
    }

    /**
     * @return \Iterator<int, string>
     */
    #[Override]
    public function lines(int $start = 0, ?int $end = null): Iterator
    {
        $file = new SplFileObject((string) $this);
        $file->seek($start);
        $lineNumber = $start;
        while ($file->valid() && (!$end || $lineNumber <= $end)) {
            ++$lineNumber;

            yield $lineNumber => rtrim($file->getCurrentLine());
            $file->next();
        }
    }

    #[Override]
    public function open(): Resource
    {
        $resource = fopen((string) $this, 'r');
        if (!$resource) {
            throw new Exception('Unable to open file: ' . $this);
        }

        return new Resource($resource);
    }

    #[Override]
    public function read(): string
    {
        $contents = file_get_contents((string) $this);
        if ($contents === false) {
            throw new Exception('Unable to read file: ' . $this);
        }

        return $contents;
    }

    #[Override]
    public function readJson(): mixed
    {
        return json_decode($this->read(), false, 512, JSON_THROW_ON_ERROR);
    }

    #[Override]
    public function realpath(): static
    {
        return new static(realpath((string) $this));
    }

    public function withoutPrefix(self $prefix): self
    {
        return new self(trim(str_replace((string) $prefix, '', (string) $this), \DIRECTORY_SEPARATOR));
    }

    #[Override]
    public function write(string $content): void
    {
        $dirname = \dirname((string) $this);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0o777, true);
        }
        $tmpName = tempnam($dirname, 'fsp-write');
        if ($tmpName === false) {
            throw new Exception('Failed to create temporary file.');
        }
        $success = file_put_contents($tmpName, $content);
        if (!$success) {
            throw new Exception('Failed to write file.');
        }
        rename($tmpName, (string) $this);
    }

    #[Override]
    public function writeJson(array $array): void
    {
        $this->write(json_encode($array, JSON_THROW_ON_ERROR));
    }

    public function moveInto(self $destination): static
    {
        if (!$destination->exists()) {
            throw new Exception('Destination does not exist.');
        }
        if (!$destination->isDirectory()) {
            throw new Exception('Destination is not a directory.');
        }
        $destination = $destination->join($this->basename());
        rename((string) $this, (string) $destination);

        /** @var static */
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
            throw new Exception('Failed to rename file.');
        }

        /** @var static */
        return $newPath;
    }

    #[Override]
    public function basename(): string
    {
        return pathinfo((string) $this, PATHINFO_BASENAME);
    }

    #[Override]
    public function mimeType(): string
    {
        $mimeType = mime_content_type((string) $this);
        if ($mimeType === false) {
            throw new Exception('Unable to detect mime type for: ' . $this);
        }

        return $mimeType;
    }

    #[Override]
    public function extensionFromMimeType(): string
    {
        $mimeType = $this->mimeType();

        return match ($mimeType) {
            'image/avif' => 'avif',
            'image/webp' => 'webp',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'video/mp4' => 'mp4',
            'video/ogg' => 'ogg',
            'video/webm' => 'webm',
            default => explode('+', explode('/', $mimeType, 2)[1] ?? '', 2)[0],
        };
    }
}
