<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Contracts;

use Meteia\Authentication\RequestingUser;

interface RequestContext
{
    public function requestingUser(): RequestingUser;
}
