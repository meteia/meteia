<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

class Content implements \Stringable
{
    public function __construct(private readonly string $name, private readonly string $content)
    {
    }

    public function __toString(): string
    {
        return <<<EOF
            <meta name="{$this->name}" content="{$this->content}">
            EOF;
    }
}
