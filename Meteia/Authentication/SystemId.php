<?php

declare(strict_types=1);

namespace Meteia\Authentication;

use Override;

final readonly class SystemId implements UserId
{
    public function __construct(
        private string $name,
    ) {}

    #[Override]
    public function equals(UserId $other): bool
    {
        return $other instanceof self && $other->name === $this->name;
    }

    #[Override]
    public function asString(): string
    {
        return "system:{$this->name}";
    }
}
