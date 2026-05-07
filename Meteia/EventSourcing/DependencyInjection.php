<?php

declare(strict_types=1);

use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\GlobalEventStream;
use Meteia\EventSourcing\PdoEventStream;
use Meteia\EventSourcing\PdoGlobalEventStream;

return [
    EventStream::class => static fn(PdoEventStream $pdoEventStream): EventStream => $pdoEventStream,
    GlobalEventStream::class => static fn(
        PdoGlobalEventStream $pdoGlobalEventStream,
    ): GlobalEventStream => $pdoGlobalEventStream,
];
