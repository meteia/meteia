<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

readonly class Charset implements \Stringable
{
    public function __construct(
        private string|\Stringable $characterSet = 'UTF-8',
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return sprintf('<meta charset="%s">', $this->characterSet);
    }
}
