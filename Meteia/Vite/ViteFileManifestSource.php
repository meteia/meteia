<?php

declare(strict_types=1);

namespace Meteia\Vite;

use Meteia\Resources\ManifestCache;
use Meteia\Resources\ManifestSource;
use Meteia\Resources\ResourceManifestPath;
use Override;

final readonly class ViteFileManifestSource implements ManifestSource
{
    public function __construct(
        private ResourceManifestPath $path,
        private ManifestCache $cache,
    ) {}

    #[Override]
    public function entries(): array
    {
        /** @var array<string, array{file?: string, imports?: list<string>, css?: list<string>}> $entries */
        return $this->cache->entriesAt($this->path);
    }
}
