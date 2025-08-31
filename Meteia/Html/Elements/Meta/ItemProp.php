<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

class ItemProp implements \Stringable
{
    public function __construct(
        private readonly string $name,
        private readonly string $content,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return <<<EOF
        <meta itemprop="{$this->name}" content="{$this->content}">
        EOF;
    }
}
