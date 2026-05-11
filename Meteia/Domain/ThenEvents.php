<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\EventSourcing\PendingEvents;

trait ThenEvents
{
    /** @var list<DomainEvent> */
    private array $__pendingEvents = [];

    public function causes(DomainEvent $event): self
    {
        return $this->appendEvent($event);
    }

    public function commitEventsIn(UnitOfWorkContext $unitOfWorkContext): self
    {
        $unitOfWorkContext->caused(new PendingEvents($this->__pendingEvents));

        return $this->withoutPendingEvents();
    }

    private function appendEvent(DomainEvent $event): self
    {
        $copy = clone $this;
        $copy->__pendingEvents[] = $event;

        return $copy;
    }

    private function withoutPendingEvents(): self
    {
        $copy = clone $this;
        $copy->__pendingEvents = [];

        return $copy;
    }
}
