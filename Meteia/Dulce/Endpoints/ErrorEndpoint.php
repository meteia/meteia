<?php

declare(strict_types=1);

namespace Meteia\Dulce\Endpoints;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorEndpoint
{
    public function response(Throwable $throwable, ServerRequestInterface $request): ResponseInterface;
}
