<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling\Endpoints;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorEndpoint
{
    public function response(\Throwable $throwable, ServerRequestInterface $request): ResponseInterface;
}
