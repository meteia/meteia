<?php

declare(strict_types=1);

use Meteia\Bootstrap\ApplicationPath;
use Meteia\Resources\InProcessManifestCache;
use Meteia\Resources\ManifestCache;
use Meteia\Resources\ManifestSource;
use Meteia\Resources\ResourceManifestPath;
use Meteia\Resources\Resources;
use Meteia\Vite\ViteFileManifestSource;
use Meteia\Vite\ViteManifest;

return [
    ResourceManifestPath::class =>
        static fn(ApplicationPath $applicationPath) => new ResourceManifestPath($applicationPath->join(
            'public/dist/.vite/manifest.json',
        )),
    ManifestCache::class => InProcessManifestCache::class,
    ManifestSource::class => ViteFileManifestSource::class,
    Resources::class => ViteManifest::class,
];
