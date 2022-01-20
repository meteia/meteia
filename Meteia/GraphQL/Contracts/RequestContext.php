<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Contracts;


class RequestContext
{
    public function __construct(public RequestingUser $user)
    {
    }
}
