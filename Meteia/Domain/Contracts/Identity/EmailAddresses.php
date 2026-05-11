<?php

declare(strict_types=1);

namespace Meteia\Domain\Contracts\Identity;

interface EmailAddresses
{
    /**
     * @return array<string, string>
     */
    public function toArray(): array;
}
