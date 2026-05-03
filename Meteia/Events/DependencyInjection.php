<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyEventInbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyEventOutbox;
use Meteia\Events\EventInbox;
use Meteia\Events\EventOutbox;

return [
    EventOutbox::class => BunnyEventOutbox::class,
    EventInbox::class => BunnyEventInbox::class,
];
