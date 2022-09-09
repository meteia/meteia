<?php

declare(strict_types=1);

namespace Meteia\Http\GraphQL;

use Exception;
use GraphQL\Type\Definition\ObjectType;
use Meteia\Http\EndpointMap;
use Meteia\Http\NamedEndpoint;

class Link extends ObjectType
{
    public function __construct(
        private readonly EndpointMap $endpointMap,
    ) {
        parent::__construct([
            'fields' => [
                'text' => self::nonNull(self::string()),
                'uri' => self::nonNull(self::string()),
                'path' => self::nonNull(self::string()),
            ],
        ]);
    }

    public function for(string $endpoint): array
    {
        if (!is_subclass_of($endpoint, NamedEndpoint::class)) {
            throw new Exception('Invalid endpoint');
        }

        return [
            'text' => $endpoint::name(),
            'path' => $this->endpointMap->path($endpoint),
            'uri' => $this->endpointMap->uri($endpoint),
        ];
    }
}
