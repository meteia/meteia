<?php

declare(strict_types=1);

use Meteia\Application\RepositoryPath;
use Meteia\Cache\Configuration\CacheDirectory;
use Meteia\Cache\Configuration\CacheHmacSecretKey;
use Meteia\Configuration\Configuration;

return [
    CacheHmacSecretKey::class => static fn (Configuration $configuration) => CacheHmacSecretKey::fromToken(
        $configuration->string('CACHE_HMAC_SECRET_KEY', ''),
    ),
    CacheDirectory::class => static fn (
        Configuration $configuration,
        RepositoryPath $repositoryPath,
    ) => new CacheDirectory($configuration->string('CACHE_DIRECTORY', $repositoryPath->join('cache'))),
];
