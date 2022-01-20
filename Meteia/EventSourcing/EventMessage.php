<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\Domain\Contracts\AggregateRoot;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

class EventMessage
{
    public function __construct(
        public AggregateRootId $aggregateRootId,
        public DomainEvent $event,
        public int $sequence,
    ) {
    }

    public function publishTo(EventBus $rabbitMQExchange): void
    {
        $rabbitMQExchange->publish($this->event);
    }

    public function appendTo(EventStream $eventStream, CausationId $causationId, CorrelationId $correlationId): void
    {
        $eventStream->append(
            $this->aggregateRootId,
            $this->sequence,
            $this->event::eventTypeId(),
            $this->event,
            $causationId,
            $correlationId,
        );
    }

    /**
     * @param AggregateRoot|EventSourcing $target
     */
    public function applyTo(AggregateRoot $target): void
    {
        $target->handleEventMessage(
            $this->aggregateRootId,
            $this->event,
            $this->sequence,
        );
    }
}
