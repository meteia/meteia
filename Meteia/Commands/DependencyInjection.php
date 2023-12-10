<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyCommandBus;
use Meteia\Commands\CommandBus;

return [
    CommandBus::class => BunnyCommandBus::class,
];
