<?php

declare(strict_types=1);

use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\PdoEventStream;

return [
    EventStream::class => fn (PdoEventStream $pdoEventStream): EventStream => $pdoEventStream,
];
