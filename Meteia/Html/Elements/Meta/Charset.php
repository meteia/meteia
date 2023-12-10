<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

class Charset implements \Stringable
{
    public function __construct(private readonly string $characterSet = 'UTF-8')
    {
    }

    public function __toString(): string
    {
        return <<<EOF
            <meta charset="{$this->characterSet}">
            EOF;
    }
}
