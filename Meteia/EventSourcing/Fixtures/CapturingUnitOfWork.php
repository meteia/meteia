<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Domain\PendingCommands;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

/**
 * @internal
 */
final class CapturingUnitOfWork implements UnitOfWork
{
    /** @var list<class-string> */
    private array $eventClasses = [];

    #[Override]
    public function caused(PendingEvents $events): void
    {
        /** @var PendingEvent $event */
        foreach ($events as $event) {
            $this->eventClasses[] = $event->event()::class;
        }
    }

    #[Override]
    public function wantsTo(PendingCommands $commands): void {}

    #[Override]
    public function complete(MessageScope $scope): void {}

    /**
     * @return list<class-string>
     */
    public function eventClasses(): array
    {
        return $this->eventClasses;
    }
}
