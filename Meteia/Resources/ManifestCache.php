<?php

declare(strict_types=1);

namespace Meteia\Resources;

use Meteia\ValueObjects\Contracts\Path;

interface ManifestCache
{
    /**
     * @return array<string, mixed>
     */
    public function entriesAt(Path $manifest): array;
}
