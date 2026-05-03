<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandInbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandOutbox;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandOutbox;

return [
    CommandOutbox::class => BunnyCommandOutbox::class,
    CommandInbox::class => BunnyCommandInbox::class,
];
