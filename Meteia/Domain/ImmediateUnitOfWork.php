<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Commands\CommandOutbox;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Events\EventOutbox;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\EventMessage;
use Meteia\EventSourcing\EventMessages;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

class ImmediateUnitOfWork implements UnitOfWork
{
    private EventMessages $pendingEventMessages;

    private CommandMessages $pendingCommandMessages;

    public function __construct(
        private EventStream $eventStream,
        private IssuedCommands $issuedCommands,
        private EventOutbox $eventOutbox,
        private CommandOutbox $commandOutbox,
    ) {
        $this->pendingEventMessages = new EventMessages();
        $this->pendingCommandMessages = new CommandMessages();
    }

    #[\Override]
    public function caused(EventMessages $eventMessages): void
    {
        $this->pendingEventMessages = $this->pendingEventMessages->merge($eventMessages);
    }

    #[\Override]
    public function complete(CausationId $causationId, CorrelationId $correlationId): void
    {
        /** @var EventMessage $eventMessage */
        foreach ($this->pendingEventMessages as $eventMessage) {
            $eventMessage->appendTo($this->eventStream, $causationId, $correlationId);
            $eventMessage->publishTo($this->eventOutbox);
        }
        $this->pendingEventMessages = new EventMessages();

        /** @var CommandMessage $commandMessage */
        foreach ($this->pendingCommandMessages as $commandMessage) {
            $commandMessage->appendTo($this->issuedCommands);
            $commandMessage->publishTo($this->commandOutbox);
        }
        $this->pendingCommandMessages = new CommandMessages();
    }

    #[\Override]
    public function wantsTo(CommandMessages $commandMessages): void
    {
        $this->pendingCommandMessages = $this->pendingCommandMessages->merge($commandMessages);
    }
}
