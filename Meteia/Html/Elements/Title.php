<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Children;
use Meteia\Html\Component;
use Meteia\Html\Node;

class Title implements Component
{
    protected string $prefix;

    protected string $suffix;

    public function __construct(
        private string $title,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return Children::of('<title>' . html($this->title) . '</title>');
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
