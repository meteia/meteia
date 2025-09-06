<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

readonly class ItemProp implements \Stringable
{
    public function __construct(
        private string|\Stringable $name,
        private string|\Stringable $content,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return sprintf('<meta itemprop="%s" content="%s">', $this->name, $this->content);
    }
}
