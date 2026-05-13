<?php

declare(strict_types=1);

namespace Meteia\Commands;

use DateTimeImmutable;

interface DelayedCommandOutbox
{
    public function publishAt(Command $command, DateTimeImmutable $when): void;
}
