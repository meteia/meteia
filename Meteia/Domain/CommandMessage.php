<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Commands\Command;
use Meteia\Commands\CommandOutbox;
use Meteia\Domain\Contracts\AggregateRoot;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class CommandMessage
{
    public function __construct(
        private PendingCommand $pending,
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private \DateTimeImmutable $issuedAt,
    ) {}

    public function command(): Command
    {
        return $this->pending->command();
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->pending->aggregateRootId();
    }

    public function causationId(): CausationId
    {
        return $this->causationId;
    }

    public function correlationId(): CorrelationId
    {
        return $this->correlationId;
    }

    public function issuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function publishTo(CommandOutbox $outbox): void
    {
        $outbox->publish($this->command());
    }

    public function appendInto(IssuedCommands $issuedCommands): void
    {
        $issuedCommands->append($this->metadata(), $this->command());
    }

    public function applyTo(AggregateRoot $target): void
    {
        $target->handleCommandMessage($this->command(), $this->metadata());
    }

    public function metadata(): CommandMetadata
    {
        return new CommandMetadata($this->aggregateRootId(), $this->causationId, $this->correlationId, $this->issuedAt);
    }
}
