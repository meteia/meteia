<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\ObjectStorage\AccessKey;
use Meteia\ObjectStorage\BucketName;
use Meteia\ObjectStorage\Endpoint;
use Meteia\ObjectStorage\Region;
use Meteia\ObjectStorage\SecretKey;

return [
    AccessKey::class => function (Configuration $configuration): AccessKey {
        return new AccessKey($configuration->string('OBJECT_STORAGE_ACCESS_KEY', ''));
    },
    SecretKey::class => function (Configuration $configuration): SecretKey {
        return new SecretKey($configuration->string('OBJECT_STORAGE_SECRET_KEY', ''));
    },
    BucketName::class => function (Configuration $configuration): BucketName {
        return new BucketName($configuration->string('OBJECT_STORAGE_BUCKET', ''));
    },
    Endpoint::class => function (Configuration $configuration): Endpoint {
        return new Endpoint($configuration->string('OBJECT_STORAGE_ENDPOINT', ''));
    },
    Region::class => function (Configuration $configuration): Region {
        return new Region($configuration->string('OBJECT_STORAGE_REGION', ''));
    },
];
