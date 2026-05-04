<?php

declare(strict_types=1);

namespace Meteia\Html\Elements;

use Meteia\Html\Children;
use Meteia\Html\Component;
use Meteia\Html\Node;

final class Title implements Component
{
    public function __construct(
        private string $title,
    ) {}

    #[\Override]
    public function render(): Node
    {
        return Children::of('<title>' . html($this->title) . '</title>');
    }

    public function rename(string $title): void
    {
        $this->title = $title;
    }
}
