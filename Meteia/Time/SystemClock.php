<?php

declare(strict_types=1);

namespace Meteia\Time;

final readonly class SystemClock implements Clock
{
    #[\Override]
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
