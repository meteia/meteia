<?php

declare(strict_types=1);

use Meteia\Time\Clock;
use Meteia\Time\SystemClock;

return [
    Clock::class => SystemClock::class,
];
