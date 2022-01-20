<?php

declare(strict_types=1);

namespace Meteia\Domain\ValueObjects;

use Meteia\Domain\Contracts\ArrayValueObject;
use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\Contracts\PrimitiveValueObject;
use Meteia\Domain\Exceptions\ImmutableValueObjectException;
use Meteia\Domain\Exceptions\InvalidValueObjectException;

abstract class ImmutableArrayValueObject implements PrimitiveValueObject, ArrayValueObject
{
    public const TYPE = 'ArrayTypeNotDefined';

    protected $values;

    public function __construct(array $values = [])
    {
        if (!is_array($values)) {
            throw new InvalidValueObjectException($values, ['array']);
        }
        $this->guardType($values);
        $this->values = $values;
    }

    public function compareTo(Comparable $other)
    {
        return count(array_diff($this->toNative(), $other->toNative()));
    }

    public function toNative()
    {
        return $this->values;
    }

    public function __toString()
    {
        return join(', ', $this->values);
    }

    public function getIterator()
    {
        foreach ($this->values as $key => $values) {
            yield $key => $values;
        }
    }

    protected function guardType($values)
    {
        foreach ($values as $value) {
            if (!is_a($value, static::TYPE)) {
                throw new InvalidValueObjectException(get_class($value), [static::TYPE]);
            }
        }
    }

    public function push($value)
    {
        $copy = $this->values;
        $copy[] = $value;

        return new static($copy);
    }

    public function merge(ImmutableArrayValueObject $other)
    {
        return new static(array_merge($this->values, $other->values));
    }

    public function toArray()
    {
        return $this->values;
    }

    public function count()
    {
        return count($this->values);
    }

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new ImmutableValueObjectException();
    }

    public function offsetUnset($offset)
    {
        throw new ImmutableValueObjectException();
    }

    public function jsonSerialize()
    {
        return $this->toNative();
    }
}
