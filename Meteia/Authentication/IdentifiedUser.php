<?php

declare(strict_types=1);

namespace Meteia\Authentication;

use Override;

final readonly class IdentifiedUser implements RequestingUser
{
    public function __construct(
        private UserId $userId,
    ) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    #[Override]
    public function pick(mixed $whenAnonymous, mixed $whenAuthenticated): mixed
    {
        return $whenAuthenticated;
    }

    #[Override]
    public function fold(callable $whenAnonymous, callable $whenAuthenticated): mixed
    {
        return $whenAuthenticated($this->userId);
    }
}
