<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandInbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandOutbox;
use Meteia\Commands\CommandBus;
use Meteia\Commands\CommandDeliveries;
use Meteia\Commands\CommandHandlers;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandOutbox;
use Meteia\Commands\Commands;
use Meteia\Commands\DelayedCommandOutbox;
use Meteia\Commands\DueDelayedCommands;
use Meteia\Commands\InProcessCommandBus;
use Meteia\Commands\PdoDelayedCommandOutbox;
use Meteia\Commands\PdoDueDelayedCommands;
use Meteia\Commands\PsrCommandHandlers;
use Meteia\Commands\PsrCommands;

return [
    CommandOutbox::class => BunnyCommandOutbox::class,
    CommandDeliveries::class => BunnyCommandOutbox::class,
    CommandInbox::class => BunnyCommandInbox::class,
    DelayedCommandOutbox::class => PdoDelayedCommandOutbox::class,
    DueDelayedCommands::class => PdoDueDelayedCommands::class,
    Commands::class => PsrCommands::class,
    CommandHandlers::class => PsrCommandHandlers::class,
    CommandBus::class => InProcessCommandBus::class,
];
