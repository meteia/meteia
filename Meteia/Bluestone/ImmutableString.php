<?php

declare(strict_types=1);

namespace Meteia\Bluestone;

use Meteia\Bluestone\Contracts\Renderable;

class ImmutableString implements Renderable
{
    /**
     * @var string
     */
    private $string;

    public function __construct(string $string = '')
    {
        $this->string = $string;
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
