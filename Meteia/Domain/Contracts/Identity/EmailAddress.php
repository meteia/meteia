<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts\Identity;

interface EmailAddress
{
    public function getAddress(): string;

    public function getDisplayName(): string;
}
