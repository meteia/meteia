<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use Meteia\GraphQL\Contracts\RequestContext;
use Meteia\GraphQL\Contracts\Resolver;

/**
 * @mixin Resolver
 */
trait ResolveWith
{
    /**
     * @return array{type: \GraphQL\Type\Definition\NonNull, resolve: callable}
     */
    public function resolveWithArg(string $propertyName, mixed $default): array
    {
        /** @var NullableType&Type $self */
        $self = $this;

        return [
            'type' => Type::nonNull($self),
            'resolve' => fn(mixed $root, array $args, RequestContext $requestContext) => $this->data(
                $args[$propertyName] ?? $default,
                $args,
                $requestContext,
            ),
        ];
    }

    /**
     * @return array{type: \GraphQL\Type\Definition\NonNull, resolve: callable}
     */
    public function resolveWithRoot(string $propertyName, mixed $default): array
    {
        /** @var NullableType&Type $self */
        $self = $this;

        return [
            'type' => Type::nonNull($self),
            'resolve' => fn(mixed $root, array $args, RequestContext $requestContext) => $this->data(
                \is_object($root) ? $root->{$propertyName} ?? $default : $default,
                $args,
                $requestContext,
            ),
        ];
    }
}
