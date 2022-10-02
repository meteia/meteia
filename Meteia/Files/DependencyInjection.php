<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Files\Configuration\AccessKey;
use Meteia\Files\Configuration\BucketName;
use Meteia\Files\Configuration\ContentAddressableStorageSecretKey;
use Meteia\Files\Configuration\Endpoint;
use Meteia\Files\Configuration\Region;
use Meteia\Files\Configuration\SecretKey;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\LocalStorage;

return [
    AccessKey::class => function (Configuration $configuration): AccessKey {
        return new AccessKey($configuration->string('METEIA_FILES_OBJECT_STORAGE_ACCESS_KEY', ''));
    },
    SecretKey::class => function (Configuration $configuration): SecretKey {
        return new SecretKey($configuration->string('METEIA_FILES_OBJECT_STORAGE_SECRET_KEY', ''));
    },
    BucketName::class => function (Configuration $configuration): BucketName {
        return new BucketName($configuration->string('METEIA_FILES_OBJECT_STORAGE_BUCKET', ''));
    },
    Endpoint::class => function (Configuration $configuration): Endpoint {
        return new Endpoint($configuration->string('METEIA_FILES_OBJECT_STORAGE_ENDPOINT', ''));
    },
    Region::class => function (Configuration $configuration): Region {
        return new Region($configuration->string('METEIA_FILES_OBJECT_STORAGE_REGION', ''));
    },
    ContentAddressableStorageSecretKey::class => function (Configuration $configuration) {
        return ContentAddressableStorageSecretKey::fromToken($configuration->string('METEIA_FILES_CONTENT_ADDRESSABLE_STORAGE_SECRET_KEY', 'invalid'));
    },
    Storage::class => LocalStorage::class,
];
