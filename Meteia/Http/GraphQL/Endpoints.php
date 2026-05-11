<?php

declare(strict_types=1);

namespace Meteia\Http\GraphQL;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use Meteia\GraphQL\Contracts\QueryField;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;
use Meteia\Http\NamedEndpoints;
use Override;

/**
 * @extends ListOfType<NonNull<Link>>
 */
class Endpoints extends ListOfType implements QueryField, Resolver
{
    public function __construct(
        private readonly Link $link,
        private readonly NamedEndpoints $namedEndpoints,
    ) {
        parent::__construct(self::nonNull($link));
    }

    #[Override]
    public function data(mixed $root, array $args, RequestContext $requestContext): array
    {
        $set = $requestContext->requestingUser()->pick($args['anon'], $args['user']);
        if (!is_array($set)) {
            return [];
        }

        $links = array_map(function (mixed $slug): ?array {
            if (!is_string($slug)) {
                return null;
            }
            $endpoint = $this->namedEndpoints->forKey($slug);
            if ($endpoint === null) {
                return null;
            }

            return $this->link->for($endpoint);
        }, $set);

        return array_values(array_filter($links));
    }

    public function args(): array
    {
        $allowed = new EnumType([
            'name' => 'EndpointName',
            'values' => array_values($this->namedEndpoints->keys()),
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
