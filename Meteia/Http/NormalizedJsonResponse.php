<?php

declare(strict_types=1);

namespace Meteia\Http;

use Meteia\Http\Responses\BinaryResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

readonly class NormalizedJsonResponse
{
    public function __construct(
        private Serializer $serializer,
    ) {
    }

    public function from(object $object): ResponseInterface
    {
        return new BinaryResponse(
            $this->serializer->serialize($object, 'json'),
            200,
            ['Content-Type' => 'application/json'],
        );
    }
}
