<?php

declare(strict_types=1);

use Meteia\Application\ApplicationPath;
use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Application\ApplicationResourcesManifestPath;
use Meteia\Application\RepositoryPath;
use Meteia\Configuration\Configuration;

return [
    ApplicationResourcesManifestPath::class => static fn (
        ApplicationPath $applicationPath,
    ) => new ApplicationResourcesManifestPath($applicationPath->join('public/dist/.vite/manifest.json')),
    ApplicationResourcesBaseUri::class => static fn (
        ApplicationResourcesManifestPath $applicationResourcesManifestPath,
        Configuration $configuration,
    ) => new ApplicationResourcesBaseUri($configuration->string('RESOURCES_BASE_URI', '/')),
    ApplicationPublicDir::class => static fn (ApplicationPath $applicationPath) => new ApplicationPublicDir(
        $applicationPath->join('public'),
    ),
    RepositoryPath::class => static fn (ApplicationPath $applicationPath) => new RepositoryPath($applicationPath),
];
