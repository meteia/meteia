<?php

declare(strict_types=1);

namespace Meteia\Time;

use DateInterval;
use DateTimeImmutable;
use Override;

final class FrozenClock implements Clock
{
    public function __construct(
        private DateTimeImmutable $instant,
    ) {}

    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->instant;
    }

    public function advance(DateInterval $interval): void
    {
        $this->instant = $this->instant->add($interval);
    }

    public function set(DateTimeImmutable $instant): void
    {
        $this->instant = $instant;
    }
}
