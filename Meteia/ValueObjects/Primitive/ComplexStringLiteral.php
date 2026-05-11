<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Primitive;

use Stringable;

/**
 * WIP
 * Class StringLiteral.
 */
class ComplexStringLiteral
{
    private string $value;

    /**
     * Returns a StringLiteral object given a PHP native string or StringLiteral as parameter(s).
     */
    public function __construct(string|Stringable ...$strings)
    {
        $string = '';
        foreach ($strings as $piece) {
            $string .= (string) $piece;
        }

        $this->value = $string;
    }

    public function __toString(): string
    {
        return $this->string();
    }

    public function isEmpty(): bool
    {
        return $this->length() === 0;
    }

    public function length(): int
    {
        return \strlen($this->value);
    }

    /**
     * Find the position of the first occurrence of a substring in a string.
     */
    public function indexOf(string|Stringable $needle, int $offset = 0): false|int
    {
        return strpos($this->value, (string) $needle, $offset);
    }

    /**
     * Split a string by string.
     *
     * @return list<self>
     */
    public function split(string|Stringable $delimiter, int $limit = 100): array
    {
        $strings = explode((string) $delimiter, $this->value, $limit);
        $stringLiterals = [];
        foreach ($strings as $string) {
            $stringLiterals[] = new self($string);
        }

        return $stringLiterals;
    }

    /**
     * Return the sub string from $start to a $length.
     */
    public function slice(int $start, int $length = 100): self
    {
        return new self(mb_substr($this->value, $start, $length));
    }

    /**
     * Replace all occurrences of the search string with the replacement string.
     *
     * @param array<array-key, string>|string $search
     * @param array<array-key, string>|string $replace
     */
    public function replace(array|string $search, array|string $replace): self
    {
        $replaced = str_replace($search, $replace, $this->value);

        return new self($replaced);
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string.
     */
    public function trim(string $charList = " \t\n\r\0\x0B"): self
    {
        return new self(trim($this->value, $charList));
    }

    public function string(): string
    {
        return $this->value;
    }

    protected function getValue(): string
    {
        return $this->value;
    }
}
