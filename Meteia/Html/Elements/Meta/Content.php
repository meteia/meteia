<?php

declare(strict_types=1);

namespace Meteia\Html\Elements\Meta;

class Content implements \Stringable
{
    public function __construct(private readonly string|\Stringable $name, private readonly string|\Stringable $content)
    {
    }

    public function __toString(): string
    {
        return <<<EOF
            <meta name="{$this->name}" content="{$this->content}">
            EOF;
    }
}
