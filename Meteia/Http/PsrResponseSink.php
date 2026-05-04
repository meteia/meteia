<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ResponseInterface;

use function Meteia\Http\Functions\send;

final readonly class PsrResponseSink implements ResponseSink
{
    #[\Override]
    public function send(ResponseInterface $response): void
    {
        send($response);
    }
}
