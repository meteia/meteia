<?php

declare(strict_types=1);

namespace Meteia\Html;

class Metadata implements \Stringable
{
    private array $tags = [];

    public function __toString()
    {
        return implode('', $this->tags);
    }

    public function include(\Stringable $view): void
    {
        $this->tags[] = $view;
    }
}
