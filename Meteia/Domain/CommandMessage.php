<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Commands\Command;
use Meteia\Domain\Contracts\AggregateRoot;
use Meteia\Domain\Transitional\RabbitMQCommandExchange;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\EventSourcing\EventSourcing;

readonly class CommandMessage
{
    public function __construct(
        public AggregateRootId $aggregateRootId,
        public Command $command,
    ) {}

    public function publishTo(RabbitMQCommandExchange $rabbitMQExchange): void
    {
        $rabbitMQExchange->publish($this->command);
    }

    public function appendTo(IssuedCommands $issuedCommands): void
    {
        $issuedCommands->append($this->aggregateRootId, $this->command, new \DateTime());
    }

    /**
     * @param AggregateRoot|EventSourcing $target
     */
    public function applyTo(AggregateRoot $target): void
    {
        $target->handleCommandMessage($this->command);
    }
}
