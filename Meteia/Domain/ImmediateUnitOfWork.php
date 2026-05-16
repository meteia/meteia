<?php

declare(strict_types=1);

namespace Meteia\Domain;

use DateTimeImmutable;
use Meteia\Commands\CommandOutbox;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\EventSourcing\AnyVersion;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

class ImmediateUnitOfWork implements UnitOfWork
{
    private PendingEvents $pendingEvents;

    private PendingCommands $pendingCommands;

    public function __construct(
        private EventStream $eventStream,
        private IssuedCommands $issuedCommands,
        private PublishedEvents $publishedEvents,
        private CommandOutbox $commandOutbox,
    ) {
        $this->pendingEvents = new PendingEvents();
        $this->pendingCommands = new PendingCommands();
    }

    #[Override]
    public function caused(PendingEvents $events): void
    {
        $this->pendingEvents = $this->pendingEvents->merge($events);
    }

    #[Override]
    public function wantsTo(PendingCommands $commands): void
    {
        $this->pendingCommands = $this->pendingCommands->merge($commands);
    }

    #[Override]
    public function complete(MessageScope $scope): void
    {
        $occurredAt = new DateTimeImmutable();
        $byStream = [];
        /** @var PendingEvent $pending */
        foreach ($this->pendingEvents as $pending) {
            $key = $pending->streamId()->hex();
            $byStream[$key][] = $pending->recordedWith($scope->causationId(), $scope->correlationId(), $occurredAt);
        }
        foreach ($byStream as $recorded) {
            /** @var RecordedEvent $first */
            $first = $recorded[0];
            $this->eventStream->append($first->streamId(), new AnyVersion(), ...$recorded);
            foreach ($recorded as $event) {
                $this->publishedEvents->publish(PublishedEvent::fromRecorded($event));
            }
        }
        $this->pendingEvents = new PendingEvents();

        $issuedAt = new DateTimeImmutable();
        /** @var PendingCommand $pending */
        foreach ($this->pendingCommands as $pending) {
            $message = $pending->issuedWith($scope->causationId(), $scope->correlationId(), $issuedAt);
            $message->appendInto($this->issuedCommands);
            $message->publishTo($this->commandOutbox);
        }
        $this->pendingCommands = new PendingCommands();
    }
}
