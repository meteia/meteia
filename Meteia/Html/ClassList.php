<?php

declare(strict_types=1);

namespace Meteia\Html;

final readonly class ClassList implements \Stringable
{
    /**
     * @param list<string> $tokens
     */
    public function __construct(
        public array $tokens = [],
    ) {}

    public static function of(string|self ...$values): self
    {
        $seen = [];
        foreach ($values as $value) {
            if ($value instanceof self) {
                foreach ($value->tokens as $token) {
                    $seen[$token] = true;
                }

                continue;
            }
            foreach (preg_split('/\s+/', trim($value)) ?? [] as $token) {
                if ($token === '') {
                    continue;
                }
                $seen[$token] = true;
            }
        }

        return new self(array_keys($seen));
    }

    #[\NoDiscard]
    public function add(string ...$class): self
    {
        return self::of($this, ...$class);
    }

    #[\NoDiscard]
    public function merge(self $other): self
    {
        return self::of($this, $other);
    }

    #[\Override]
    public function __toString(): string
    {
        return implode(' ', $this->tokens);
    }
}
