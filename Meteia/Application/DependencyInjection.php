<?php

declare(strict_types=1);

use Meteia\Application\CommandBus;
use Meteia\Application\CommandEndpointRegistry;
use Meteia\Application\ContainerCommandEndpointRegistry;
use Meteia\Application\InProcessCommandBus;

return [
    CommandEndpointRegistry::class => ContainerCommandEndpointRegistry::class,
    CommandBus::class => InProcessCommandBus::class,
];
