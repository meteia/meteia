<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\Domain\ValueObjects\ImmutableEvents;

trait ThenEvents
{
    /** @var DomainEvent[] */
    private $__pendingEvents = [];

    public function causes(DomainEvent $event)
    {
        return $this->appendEvent($event);
    }

    public function commitEventsIn(UnitOfWorkContext $unitOfWorkContext)
    {
        $unitOfWorkContext->commitEvents(new ImmutableEvents($this->__pendingEvents));

        return $this->withoutPendingEvents();
    }

    private function appendEvent(DomainEvent $event)
    {
        $copy = clone $this;
        $copy->__pendingEvents[] = $event;

        return $copy;
    }

    private function withoutPendingEvents()
    {
        $copy = clone $this;
        $copy->__pendingEvents = [];

        return $copy;
    }
}
