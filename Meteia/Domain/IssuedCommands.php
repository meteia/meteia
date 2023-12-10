<?php

declare(strict_types=1);

namespace Meteia\Domain;

use DateTimeInterface;
use Meteia\Commands\Command;
use Meteia\Domain\ValueObjects\AggregateRootId;

class IssuedCommands
{
    public function pending(): array
    {
        return [];
    }

    public function append(
        AggregateRootId $aggregateRootId,
        Command $command,
        DateTimeInterface $deferUntil,
    ): void {
    }
}
