<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\GraphQL\Contracts\RequestContext;

trait ResolveWith
{
    public function resolveWithArg(string $propertyName, mixed $default): array
    {
        return [
            'type' => self::nonNull($this),
            'resolve' => fn (mixed $root, array $args, RequestContext $requestContext) => $this->data($args[$propertyName] ?? $default, $args, $requestContext),
        ];
    }


    public function resolveWithRoot(string $propertyName, mixed $default): array
    {
        return [
            'type' => self::nonNull($this),
            'resolve' => fn (mixed $root, array $args, RequestContext $requestContext) => $this->data($root->{$propertyName} ?? $default, $args, $requestContext),
        ];
    }
}
