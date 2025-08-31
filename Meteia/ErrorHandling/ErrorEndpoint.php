<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling;

use Laminas\Diactoros\Response;
use Meteia\Http\Endpoint;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorEndpoint implements Endpoint
{
    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(\Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    #[\Override]
    public function response(ServerRequestInterface $request): ResponseInterface
    {
        $message = $this->throwable->getMessage();

        return new Response($message, 503);
    }
}
