<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Override;

final readonly class LiveViewSessionRejected implements LiveViewSessionVerification
{
    public function __construct(
        public string $reason,
    ) {}

    #[Override]
    public function accepted(): bool
    {
        return false;
    }
}
