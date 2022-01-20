<?php

declare(strict_types=1);

namespace Meteia\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Endpoint
{
    public function response(ServerRequestInterface $request): ResponseInterface;
}
