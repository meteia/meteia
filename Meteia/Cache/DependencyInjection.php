<?php

declare(strict_types=1);

use Meteia\Application\RepositoryPath;
use Meteia\Cache\Configuration\CacheDirectory;
use Meteia\Configuration\Configuration;

return [
    CacheDirectory::class => static fn (
        Configuration $configuration,
        RepositoryPath $repositoryPath,
    ) => new CacheDirectory($configuration->string('CACHE_DIRECTORY', $repositoryPath->join('cache'))),
];
