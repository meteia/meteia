<?php

declare(strict_types=1);

namespace Meteia\Domain;

use DateTimeImmutable;
use Meteia\Commands\Command;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class PendingCommand
{
    public function __construct(
        private AggregateRootId $aggregateRootId,
        private Command $command,
    ) {}

    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    public function command(): Command
    {
        return $this->command;
    }

    public function issuedWith(
        CausationId $causationId,
        CorrelationId $correlationId,
        DateTimeImmutable $issuedAt,
    ): CommandMessage {
        return new CommandMessage($this, $causationId, $correlationId, $issuedAt);
    }
}
