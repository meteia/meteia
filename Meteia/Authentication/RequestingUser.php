<?php

declare(strict_types=1);

namespace Meteia\Authentication;

interface RequestingUser
{
    public function isAnonymous(): bool;

    public function isSystem(): bool;

    public function userId(): UserIdentifier;
}
