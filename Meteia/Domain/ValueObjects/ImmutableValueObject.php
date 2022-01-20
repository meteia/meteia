<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\Domain\Contracts\ValueObject;
use Meteia\Domain\Exceptions\ImmutableValueObjectException;

abstract class ImmutableValueObject implements ValueObject
{
    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }

    public function __set($key, $value)
    {
        throw new ImmutableValueObjectException(get_class($this) . ' is immutable');
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

    public function getIterator()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ($key[0] !== '_') {
                yield $key => $value;
            }
        }
    }

    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->getIterator() as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function with($key, $value)
    {
        $copy = clone $this;
        $copy->$key = $value;

        return $copy;
    }
}
