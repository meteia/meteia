<?php

declare(strict_types=1);

use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\Bootstrap\RepositoryPath;

return [
    ApplicationPublicDir::class =>
        static fn(ApplicationPath $applicationPath) => new ApplicationPublicDir($applicationPath->join('public')),
    RepositoryPath::class => static fn(ApplicationPath $applicationPath) => new RepositoryPath($applicationPath),
];
