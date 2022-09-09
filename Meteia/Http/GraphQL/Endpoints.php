<?php

declare(strict_types=1);

namespace Meteia\Http\GraphQL;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ListOfType;
use Meteia\GraphQL\Contracts\QueryField;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\Http\NamedEndpoints;

class Endpoints extends ListOfType implements QueryField, Resolver
{
    public function __construct(
        private readonly Link $link,
        private readonly NamedEndpoints $namedEndpoints,
    ) {
        parent::__construct(self::nonNull($link));
    }

    public function data(mixed $root, array $args, RequestContext $requestContext): array
    {
        $set = $requestContext->requestingUser()->isAnonymous() ? $args['anon'] : $args['user'];

        $links = array_map(function ($slug) {
            $endpoint = $this->namedEndpoints->forKey($slug);
            if (!$endpoint) {
                return null;
            }

            return $this->link->for($endpoint);
        }, $set);

        return array_filter($links);
    }

    public function args(): array
    {
        $allowed = new EnumType([
            'name' => 'EndpointName',
            'values' => $this->namedEndpoints->keys(),
        ]);

        return [
            'user' => [
                'type' => self::listOf($allowed),
                'defaultValue' => [],
            ],
            'anon' => [
                'type' => self::listOf($allowed),
                'defaultValue' => [],
            ],
        ];
    }
}
