<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

class Title implements \Stringable
{
    protected string $prefix;

    protected string $suffix;

    public function __construct(private string $title)
    {
    }

    public function __toString(): string
    {
        return '<title>' . html($this->title) . '</title>';
    }

    public function prefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function set(string $title): void
    {
        $this->title = $title;
    }

    public function suffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }
}
