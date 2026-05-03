<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Resources\ResourceBaseUri;

return [
    ResourceBaseUri::class => static fn(Configuration $configuration) => new ResourceBaseUri($configuration->string(
        'RESOURCES_BASE_URI',
        '/',
    )),
];
