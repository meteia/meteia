<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

use Meteia\GraphQL\Contracts\RequestContext;

interface TableConnectionBindings
{
    public function resolveWhereBindings(mixed $root, array $args, RequestContext $requestContext): array;
}
