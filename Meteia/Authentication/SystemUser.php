<?php

declare(strict_types=1);

namespace Meteia\Authentication;

final readonly class SystemUser implements RequestingUser
{
    public function __construct(
        private SystemId $systemId,
    ) {}

    public function systemId(): SystemId
    {
        return $this->systemId;
    }

    #[\Override]
    public function pick(mixed $whenAnonymous, mixed $whenAuthenticated): mixed
    {
        return $whenAuthenticated;
    }

    #[\Override]
    public function fold(callable $whenAnonymous, callable $whenAuthenticated): mixed
    {
        return $whenAuthenticated($this->systemId);
    }
}
