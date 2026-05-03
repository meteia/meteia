<?php

declare(strict_types=1);

namespace Meteia\Vite;

use Meteia\Resources\ManifestSource;
use Meteia\Resources\ResourceManifestPath;

final class ViteFileManifestSource implements ManifestSource
{
    private ?array $entries = null;

    public function __construct(
        private readonly ResourceManifestPath $path,
    ) {}

    #[\Override]
    public function entries(): array
    {
        return $this->entries ??= $this->load();
    }

    private function load(): array
    {
        if (!$this->path->isReadable()) {
            return [];
        }

        return json_decode($this->path->read(), true, 512, JSON_THROW_ON_ERROR);
    }
}
