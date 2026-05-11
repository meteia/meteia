<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use IteratorAggregate;
use JsonSerializable;
use Meteia\ValueObjects\Errors\ValueObjectImmutable;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<string, mixed>
 */
abstract class ValueObject implements JsonSerializable, IteratorAggregate
{
    public function __get(string $key): mixed
    {
        if (isset($this->{$key})) {
            return $this->{$key};
        }

        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        throw new ValueObjectImmutable(static::class . ' is immutable');
    }

    public function __isset(string $key): bool
    {
        return isset($this->{$key});
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (strncmp($name, 'with', 4) === 0) {
            return $this->with(lcfirst(substr($name, 4)), $arguments[0] ?? null);
        }

        return null;
    }

    #[Override]
    public function getIterator(): Traversable
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (str_starts_with((string) $key, '_')) {
                continue;
            }

            yield $key => $value;
        }
    }

    #[Override]
    public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this->getIterator() as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function with(string $key, mixed $value): self
    {
        $copy = clone $this;
        $copy->{$key} = $value;

        return $copy;
    }
}
