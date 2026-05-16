<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandInbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandOutbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyDelayedCommandOutbox;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandEndpoints;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandOutbox;
use Meteia\Commands\ContainerCommandEndpoints;
use Meteia\Commands\DelayedCommandOutbox;
use Meteia\Commands\InProcessCommandBus;

return [
    CommandOutbox::class => BunnyCommandOutbox::class,
    CommandInbox::class => BunnyCommandInbox::class,
    DelayedCommandOutbox::class => BunnyDelayedCommandOutbox::class,
    CommandEndpoints::class => ContainerCommandEndpoints::class,
    CommandBus::class => InProcessCommandBus::class,
];
