<?php

declare(strict_types=1);

namespace Meteia\ValueObjects;

use Meteia\Domain\Contracts\ArrayValueObject;
use Meteia\Domain\Contracts\Comparable;
use Meteia\Domain\Contracts\PrimitiveValueObject;
use Meteia\ValueObjects\Errors\ObjectMutationProhibited;
use Meteia\ValueObjects\Errors\ValueObjectInvalid;
use Override;
use Traversable;

abstract readonly class ImmutableArrayValueObject implements PrimitiveValueObject, ArrayValueObject
{
    public const TYPE = 'ArrayTypeNotDefined';

    public function __construct(
        protected array $values = [],
    ) {
        $this->guardType($values);
    }

    #[Override]
    public function __toString()
    {
        return implode(', ', $this->values);
    }

    public function compareTo(Comparable $other)
    {
        return \count(array_diff($this->toNative(), $other->toNative()));
    }

    public function toNative()
    {
        return $this->values;
    }

    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->values as $key => $values) {
            yield $key => $values;
        }
    }

    public function push($value)
    {
        $copy = $this->values;
        $copy[] = $value;

        return new static($copy);
    }

    public function merge(self $other)
    {
        return new static(array_merge($this->values, $other->values));
    }

    public function toArray()
    {
        return $this->values;
    }

    #[Override]
    public function count(): int
    {
        return \count($this->values);
    }

    #[Override]
    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }

    #[Override]
    public function offsetGet($offset): mixed
    {
        return $this->values[$offset];
    }

    #[Override]
    public function offsetSet($offset, $value): void
    {
        throw new ObjectMutationProhibited();
    }

    #[Override]
    public function offsetUnset($offset): void
    {
        throw new ObjectMutationProhibited();
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }

    protected function guardType($values): void
    {
        foreach ($values as $value) {
            if (!is_a($value, static::TYPE)) {
                throw new ValueObjectInvalid($value::class, [static::TYPE]);
            }
        }
    }
}
