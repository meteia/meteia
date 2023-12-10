<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyEventBus;
use Meteia\Events\EventBus;

return [
    EventBus::class => BunnyEventBus::class,
];
