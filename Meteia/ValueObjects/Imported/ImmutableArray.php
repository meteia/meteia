<?php

declare(strict_types=1);

namespace Meteia\Yeso\ValueObjects;

use Meteia\Yeso\Exceptions\ArrayTypeNotDefined;
use Meteia\Yeso\Exceptions\ImproperType;
use Meteia\Yeso\Exceptions\ObjectMutationProhibited;

abstract class ImmutableArray implements \IteratorAggregate, \Countable, \ArrayAccess
{
    public const TYPE = ArrayTypeNotDefined::class;

    protected $values;

    public function __construct(array $values = [])
    {
        array_filter($values, [$this, 'guardType']);
        $this->values = $values;
    }

    #[\Override]
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

    public function appendTraversable(\Traversable $traversable): self
    {
        return $this->appendArray(iterator_to_array($traversable));
    }

    #[\Override]
    public function count()
    {
        return \count($this->values);
    }

    #[\Override]
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    #[\Override]
    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        throw new ObjectMutationProhibited();
    }

    #[\Override]
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
