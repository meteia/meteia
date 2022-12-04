<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

use Stringable;

class ImmutableString implements Stringable
{
    public function __construct(private readonly string $string = '')
    {
    }

    public function __toString()
    {
        return $this->string;
    }

    public function rendered(): string
    {
        return $this->string;
    }
}
