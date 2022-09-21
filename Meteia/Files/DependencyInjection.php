<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\DependencyInjection\Container;
use Meteia\Files\ContentAddressableStorageSecretKey;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\LocalStorage;
use Meteia\ObjectStorage\ObjectStorage;

return [
    Storage::class => function (Container $container, Configuration $configuration): Storage {
        $backend = $configuration->string('METEIA_FILES_BACKEND', 'local');

        return $container->get(match ($backend) {
            'object' => ObjectStorage::class,
            default => LocalStorage::class,
        });
    },
    ContentAddressableStorageSecretKey::class => function (Configuration $configuration) {
        return ContentAddressableStorageSecretKey::fromToken($configuration->string('METEIA_FILES_CONTENT_ADDRESSABLE_STORAGE_SECRET_KEY', 'invalid'));
    },
];
