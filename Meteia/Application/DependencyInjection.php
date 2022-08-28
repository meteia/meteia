<?php

declare(strict_types=1);

use Meteia\Application\ApplicationResourcesBaseUri;
use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\ApplicationResources;

return [
    ApplicationResourcesBaseUri::class => fn () => new ApplicationResourcesBaseUri('http://127.0.0.1:5173'),
    ApplicationResources::class => function (ApplicationResourcesBaseUri $applicationResourcesBaseUri, ApplicationPublicDir $publicDir): ApplicationResources {
        return new ApplicationResources($applicationResourcesBaseUri, $publicDir, $publicDir->join('dist/manifest.json'));
    },
];
