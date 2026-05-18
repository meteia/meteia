<?php

declare(strict_types=1);

namespace Meteia\Commands;

use DateTimeImmutable;

interface DelayedCommandOutbox
{
    /**
     * @param Command<mixed> $command
     */
    public function publishAt(Command $command, DateTimeImmutable $when): void;
}
