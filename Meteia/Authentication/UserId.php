<?php

declare(strict_types=1);

namespace Meteia\Authentication;

interface UserId
{
    public function equals(self $other): bool;

    public function asString(): string;
}
