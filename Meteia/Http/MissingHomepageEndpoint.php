<?php

declare(strict_types=1);

namespace Meteia\Http;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MissingHomepageEndpoint implements Endpoint
{
    #[\Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse('Hello, world! Use your projects DependencyInjection.php to point to a class in your project that implements the interface '
        . HomepageEndpoint::class);
    }
}
