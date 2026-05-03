<?php

declare(strict_types=1);

namespace Meteia\Resources;

use Meteia\Html\Elements\Head;

interface Resources
{
    public function requireEntry(EntryTarget $entry, Head $head): void;

    public function requireModule(string $path, Head $head): void;

    public function requireStyle(string $path, Head $head): void;
}
