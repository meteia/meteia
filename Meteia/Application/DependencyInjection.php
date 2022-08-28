<?php

declare(strict_types=1);

use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\ApplicationResources;
use Meteia\Configuration\Configuration;

return [
    ApplicationResourcesBaseUri::class => function (Configuration $configuration) {
        return new ApplicationResourcesBaseUri($configuration->string('RESOURCES_BASE_URI', ''));
    },
    ApplicationResources::class => function (ApplicationResourcesBaseUri $applicationResourcesBaseUri, ApplicationPublicDir $publicDir): ApplicationResources {
        return new ApplicationResources($applicationResourcesBaseUri, $publicDir, $publicDir->join('dist/manifest.json'));
    },
];
