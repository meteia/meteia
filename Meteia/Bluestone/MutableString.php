<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

use Meteia\Bluestone\Contracts\Renderable;

class MutableString implements Renderable
{
    public function __construct(private string $string = '')
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

    public function replaceContentWith(string $string): void
    {
        $this->string = $string;
    }
}
