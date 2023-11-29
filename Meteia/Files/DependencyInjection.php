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
use Meteia\Files\ObjectStorage;

return [
    AccessKey::class => fn (Configuration $configuration): AccessKey => new AccessKey($configuration->string('METEIA_FILES_OBJECT_STORAGE_ACCESS_KEY', '')),
    SecretKey::class => fn (Configuration $configuration): SecretKey => new SecretKey($configuration->string('METEIA_FILES_OBJECT_STORAGE_SECRET_KEY', '')),
    BucketName::class => fn (Configuration $configuration): BucketName => new BucketName($configuration->string('METEIA_FILES_OBJECT_STORAGE_BUCKET', '')),
    Endpoint::class => fn (Configuration $configuration): Endpoint => new Endpoint($configuration->string('METEIA_FILES_OBJECT_STORAGE_ENDPOINT', '')),
    Region::class => fn (Configuration $configuration): Region => new Region($configuration->string('METEIA_FILES_OBJECT_STORAGE_REGION', '')),
    ContentAddressableStorageSecretKey::class => fn (Configuration $configuration) => ContentAddressableStorageSecretKey::fromToken($configuration->string('METEIA_FILES_CONTENT_ADDRESSABLE_STORAGE_SECRET_KEY', 'invalid')),
    Storage::class => fn (Configuration $configuration) => match ($configuration->string('METEIA_FILES_STORAGE', 'local')) {
        'object' => ObjectStorage::class,
        default => LocalStorage::class,
    },
];
