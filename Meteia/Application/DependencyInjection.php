<?php

declare(strict_types=1);

use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\ApplicationResources;

return [
    ApplicationResources::class => function (ApplicationPublicDir $publicDir): ApplicationResources {
        return new ApplicationResources($publicDir, $publicDir->join('dist/manifest.json'));
    },
];
