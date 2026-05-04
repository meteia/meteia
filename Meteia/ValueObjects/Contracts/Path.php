<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Contracts;

use Meteia\Cryptography\Hash;
use Meteia\Cryptography\SecretKey;
use Meteia\ValueObjects\Identity\Resource;

interface Path extends Text
{
    public function exists(): bool;

    public function isReadable(): bool;

    public function isDirectory(): bool;

    public function basename(): string;

    public function extension(): string;

    public function mimeType(): string;

    public function extensionFromMimeType(): string;

    public function realpath(): self;

    public function join(string|\Stringable ...$paths): self;

    public function read(): string;

    public function readJson(): mixed;

    /**
     * @return \Iterator<int, string>
     */
    public function lines(int $start = 0, ?int $end = null): \Iterator;

    public function find(string ...$regex): \Iterator;

    public function hash(string $algo, ?SecretKey $hmacKey = null): Hash;

    public function open(): Resource;

    public function delete(): void;

    public function write(string $content): void;

    public function writeJson(array $array): void;
}
