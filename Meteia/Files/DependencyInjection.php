<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Files\ContentAddressableStorageSecretKey;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\LocalStorage;

return [
    Storage::class => LocalStorage::class,
    ContentAddressableStorageSecretKey::class => function (Configuration $configuration) {
        return ContentAddressableStorageSecretKey::fromToken($configuration->string('CONTENT_ADDRESSABLE_STORAGE_SECRET_KEY', 'invalid'));
    },
];
