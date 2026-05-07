<?php

declare(strict_types=1);

use Meteia\MessageStreams\Contracts\MessageStream;
use Meteia\MessageStreams\PdoMessageStream;

return [
    MessageStream::class => static fn(PdoMessageStream $pdoMessageStream): MessageStream => $pdoMessageStream,
];
