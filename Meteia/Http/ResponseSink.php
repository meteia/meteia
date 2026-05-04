<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ResponseInterface;

interface ResponseSink
{
    public function send(ResponseInterface $response): void;
}
