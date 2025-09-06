<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

abstract class MutableString
{
    public function __construct(
        private string $string = '',
    ) {}

    public function __toString()
    {
        return $this->string;
    }

    public function rendered(): string
    {
        return $this->string;
    }

    public function set(string $string): void
    {
        $this->string = $string;
    }
}
