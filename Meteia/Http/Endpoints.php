<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ServerRequestInterface;

interface Endpoints
{
    public function endpoint(ServerRequestInterface $request): Endpoint;
}
