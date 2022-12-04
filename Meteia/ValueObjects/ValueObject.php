<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use IteratorAggregate;
use JsonSerializable;
use Meteia\Domain\Errors\ValueObjectImmutable;
use Traversable;

abstract class ValueObject implements JsonSerializable, IteratorAggregate
{
    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }

    public function __set($key, $value): void
    {
        throw new ValueObjectImmutable(get_class($this) . ' is immutable');
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    public function __call($name, $arguments)
    {
        if (strncmp($name, 'with', 4) === 0) {
            return $this->with(lcfirst(substr($name, 4)), ...$arguments);
        }
    }

    public function getIterator(): Traversable
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ($key[0] !== '_') {
                yield $key => $value;
            }
        }
    }

    public function jsonSerialize(): array
    {
        $data = [];
        foreach ($this->getIterator() as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function with($key, $value): self
    {
        $copy = clone $this;
        $copy->$key = $value;

        return $copy;
    }
}
