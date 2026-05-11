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

    /**
     * @param array<array-key, object> $values
     */
    public function __construct(
        protected array $values = [],
    ) {
        $this->guardType($values);
    }

    #[Override]
    public function __toString(): string
    {
        return implode(', ', array_map(static fn(object $v): string => $v instanceof \Stringable
            ? (string) $v
            : $v::class, $this->values));
    }

    public function compareTo(Comparable $other): int
    {
        $otherNative = $other->toNative();
        \assert(\is_array($otherNative));

        return \count(array_diff($this->toNative(), $otherNative));
    }

    /**
     * @return array<array-key, object>
     */
    public function toNative(): array
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

    public function push(object $value): static
    {
        $copy = $this->values;
        $copy[] = $value;

        return new static($copy);
    }

    public function merge(self $other): static
    {
        return new static(array_merge($this->values, $other->values));
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
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

    /**
     * @param array<array-key, object> $values
     */
    protected function guardType(array $values): void
    {
        foreach ($values as $value) {
            if (!is_a($value, static::TYPE)) {
                throw new ValueObjectInvalid($value::class, [static::TYPE]);
            }
        }
    }
}
