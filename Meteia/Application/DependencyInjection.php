<?php

declare(strict_types=1);

use Meteia\Application\ApplicationPath;
use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\ApplicationResources;
use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Application\RepositoryPath;
use Meteia\Configuration\Configuration;

return [
    ApplicationResourcesBaseUri::class => static fn (Configuration $configuration) => new ApplicationResourcesBaseUri(
        $configuration->string('RESOURCES_BASE_URI', ''),
    ),
    ApplicationResources::class => static fn (
        ApplicationResourcesBaseUri $applicationResourcesBaseUri,
        ApplicationPublicDir $publicDir,
    ): ApplicationResources => new ApplicationResources(
        $applicationResourcesBaseUri,
        $publicDir,
        $publicDir->join('dist/manifest.json'),
    ),
    ApplicationPublicDir::class => static fn (ApplicationPath $applicationPath) => new ApplicationPublicDir(
        $applicationPath->join('public'),
    ),
    RepositoryPath::class => static fn (ApplicationPath $applicationPath) => new RepositoryPath($applicationPath),
];
