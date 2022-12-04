<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Meteia\Yeso\Exceptions\ArrayTypeNotDefined;
use Meteia\Yeso\Exceptions\ImproperType;
use Meteia\Yeso\Exceptions\ObjectMutationProhibited;
use Traversable;

abstract class ImmutableArray implements IteratorAggregate, Countable, ArrayAccess
{
    public const TYPE = ArrayTypeNotDefined::class;

    protected $values;

    public function __construct(array $values = [])
    {
        array_filter($values, [$this, 'guardType']);
        $this->values = $values;
    }

    public function getIterator()
    {
        foreach ($this->values as $key => $values) {
            yield $key => $values;
        }
    }

    public function push($value): self
    {
        $copy = $this->values;
        $copy[] = $value;

        return new static($copy);
    }

    public function appendArray(array $array): self
    {
        return new static(array_merge($this->values, $array));
    }

    public function appendTraversable(Traversable $traversable): self
    {
        return $this->appendArray(iterator_to_array($traversable));
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

    public function offsetSet($offset, $value): void
    {
        throw new ObjectMutationProhibited();
    }

    public function offsetUnset($offset): void
    {
        throw new ObjectMutationProhibited();
    }

    protected function guardType($value): void
    {
        if (!is_a($value, static::TYPE)) {
            throw new ImproperType($value::class, [static::TYPE]);
        }
    }
}
