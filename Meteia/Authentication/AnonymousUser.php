<?php

declare(strict_types=1);

namespace Meteia\Authentication;

final readonly class AnonymousUser implements RequestingUser
{
    #[\Override]
    public function pick(mixed $whenAnonymous, mixed $whenAuthenticated): mixed
    {
        return $whenAnonymous;
    }

    #[\Override]
    public function fold(callable $whenAnonymous, callable $whenAuthenticated): mixed
    {
        return $whenAnonymous();
    }
}
