<?php

declare(strict_types=1);

namespace Meteia\Html;

use NoDiscard;
use Stringable;

final readonly class Attrs
{
    /**
     * @param array<string, bool|string|\Stringable|int|float|null> $values
     */
    public function __construct(
        public array $values = [],
    ) {}

    /**
     * @param array<int|string, mixed> $raw
     */
    public static function from(array $raw): self
    {
        if (\count($raw) === 1 && isset($raw[0]) && \is_string($raw[0])) {
            return new self(['class' => $raw[0]]);
        }

        return new self($raw);
    }

    #[NoDiscard]
    public function with(string $name, bool|string|int|float|Stringable|null $value): self
    {
        return clone($this, ['values' => [...$this->values, $name => $value]]);
    }

    #[NoDiscard]
    public function withClass(ClassList|string $class): self
    {
        $class = $class instanceof ClassList ? $class : ClassList::of($class);
        $existing = isset($this->values['class']) ? ClassList::of((string) $this->values['class']) : ClassList::of();

        return $this->with('class', $existing->merge($class));
    }

    #[NoDiscard]
    public function merge(self $other): self
    {
        return clone($this, ['values' => [...$this->values, ...$other->values]]);
    }
}
