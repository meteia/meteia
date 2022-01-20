<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\Transitional\RabbitMQCommandExchange;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\EventBus;
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
        private EventBus $rabbitMQExchange,
        private RabbitMQCommandExchange $commandExchange,
    ) {
        $this->pendingEventMessages = new EventMessages();
        $this->pendingCommandMessages = new CommandMessages();
    }

    public function caused(EventMessages $eventMessages)
    {
        $this->pendingEventMessages = $this->pendingEventMessages->merge($eventMessages);
    }

    public function complete(CausationId $causationId, CorrelationId $correlationId)
    {
        /** @var EventMessage $eventMessage */
        foreach ($this->pendingEventMessages as $eventMessage) {
            $eventMessage->appendTo($this->eventStream, $causationId, $correlationId);
            $eventMessage->publishTo($this->rabbitMQExchange);
        }
        $this->pendingEventMessages = new EventMessages();

        /** @var CommandMessage $commandMessage */
        foreach ($this->pendingCommandMessages as $commandMessage) {
            $commandMessage->appendTo($this->issuedCommands);
            $commandMessage->publishTo($this->commandExchange);
        }
        $this->pendingCommandMessages = new CommandMessages();
    }

    public function wantsTo(CommandMessages $commandMessages)
    {
        $this->pendingCommandMessages = $this->pendingCommandMessages->merge($commandMessages);
    }
}
