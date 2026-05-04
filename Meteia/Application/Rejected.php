<?php

declare(strict_types=1);

namespace Meteia\Application;

final readonly class Rejected implements CommandResult
{
    public function __construct(
        private string $reason,
    ) {}

    public function reason(): string
    {
        return $this->reason;
    }
}
