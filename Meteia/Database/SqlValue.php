<?php

declare(strict_types=1);

namespace Meteia\Database;

use Meteia\Cryptography\Hash;
use Meteia\ValueObjects\Identity\UniqueId;

final readonly class SqlValue
{
    private const MYSQL_DATE = 'Y-m-d';

    private const MYSQL_DATETIME = 'Y-m-d H:i:s';

    public function __construct(
        private mixed $value,
    ) {}

    public function bound(): mixed
    {
        if (\is_array($this->value)) {
            return $this->boundArray();
        }
        if (!\is_object($this->value)) {
            return $this->value;
        }

        return $this->boundObject();
    }

    private function boundArray(): array
    {
        return array_map(static fn(mixed $value): mixed => new self($value)->bound(), $this->value);
    }

    private function boundObject(): mixed
    {
        if ($this->value instanceof UniqueId || $this->value instanceof Hash) {
            return $this->boundBinaryObject();
        }

        return $this->boundScalarObject();
    }

    private function boundBinaryObject(): mixed
    {
        if ($this->value instanceof UniqueId) {
            return $this->value->bytes;
        }
        if ($this->value instanceof Hash) {
            return $this->value->binary();
        }

        return $this->value;
    }

    private function boundScalarObject(): mixed
    {
        if ($this->value instanceof \DateTimeInterface) {
            return $this->boundDateTime();
        }

        return $this->boundTextObject();
    }

    private function boundTextObject(): mixed
    {
        if ($this->value instanceof \BackedEnum) {
            return $this->value->value;
        }
        if ($this->value instanceof \Stringable) {
            return (string) $this->value;
        }

        return $this->value;
    }

    private function boundDateTime(): string
    {
        if ($this->value instanceof \DateTime) {
            return $this->value->format(self::MYSQL_DATE);
        }

        \assert($this->value instanceof \DateTimeInterface, 'Date time value expected');

        return $this->value->format(self::MYSQL_DATETIME);
    }
}
