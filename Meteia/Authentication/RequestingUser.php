<?php

declare(strict_types=1);

namespace Meteia\Authentication;

interface RequestingUser
{
    /**
     * Two-arm choice based on anonymity. SystemUser collapses with IdentifiedUser.
     *
     * @template T
     *
     * @param T $whenAnonymous
     * @param T $whenAuthenticated
     *
     * @return T
     */
    public function pick(mixed $whenAnonymous, mixed $whenAuthenticated): mixed;

    /**
     * Two-arm fold; the authenticated branch receives the principal's UserId.
     * SystemUser projects its SystemId (which implements UserId) into the
     * authenticated branch.
     *
     * @template T
     *
     * @param callable(): T       $whenAnonymous
     * @param callable(UserId): T $whenAuthenticated
     *
     * @return T
     */
    public function fold(callable $whenAnonymous, callable $whenAuthenticated): mixed;
}
