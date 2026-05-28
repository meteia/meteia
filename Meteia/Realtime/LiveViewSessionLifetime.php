<?php

declare(strict_types=1);

namespace Meteia\Realtime;

final readonly class LiveViewSessionLifetime
{
    public function __construct(
        private int $seconds,
    ) {}

    public function seconds(): int
    {
        return $this->seconds;
    }
}
