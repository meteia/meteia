<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Contracts;

interface Resolver
{
    public function data(mixed $root, array $args, RequestContext $requestContext): mixed;
}
