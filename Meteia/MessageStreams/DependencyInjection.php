<?php

declare(strict_types=1);

use Meteia\MessageStreams\Contracts\MessageStream;
use Meteia\MessageStreams\PdoEventStream;

return [
    MessageStream::class => PdoEventStream::class,
];
