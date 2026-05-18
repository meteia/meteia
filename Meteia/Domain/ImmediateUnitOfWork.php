<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Commands\CommandOutbox;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\EmptyStream;
use Meteia\EventSourcing\ExactlyAt;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Throwable;

/**
 * @mago-expect analyze:kan-defect -- UoW atomicity (tx + persist-then-publish + proper ExpectedVersion) intentionally increases control-flow; correctness > metric threshold
 */
class ImmediateUnitOfWork implements UnitOfWork
{
    private PendingEvents $pendingEvents;

    private PendingCommands $pendingCommands;

    public function __construct(
        private EventStream $eventStream,
        private IssuedCommands $issuedCommands,
        private PublishedEvents $publishedEvents,
        private CommandOutbox $commandOutbox,
        private ?ExtendedPdoInterface $db = null,
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
    /**
     * @mago-expect analyze:halstead -- complete() now coordinates tx + versioned persist + post-commit publish for atomic UoW; the added structure is required for correctness
     */
    public function complete(MessageScope $scope): void
    {
        try {
            $this->doFlush($scope);
        } finally {
            $this->pendingEvents = new PendingEvents();
            $this->pendingCommands = new PendingCommands();
        }
    }

    private function doFlush(MessageScope $scope): void
    {
        $eventsToPersistAndPublish = $this->buildEventsToPersist($scope);
        $commandsToPersistAndPublish = $this->buildCommandsToPersist($scope);

        if ($eventsToPersistAndPublish === [] && $commandsToPersistAndPublish === []) {
            return;
        }

        if ($this->db !== null) {
            $this->db->beginTransaction();
        }

        try {
            $this->persistEvents($eventsToPersistAndPublish);
            $this->persistCommands($commandsToPersistAndPublish);

            if ($this->db !== null && $this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($this->db !== null && $this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }

        // Publish only after successful commit (when tx was used) or immediately for non-tx Immediate usage.
        $this->publishEvents($eventsToPersistAndPublish);
        $this->publishCommands($commandsToPersistAndPublish);
    }

    /**
     * @return array<string, list<RecordedEvent>>
     */
    private function buildEventsToPersist(MessageScope $scope): array
    {
        $occurredAt = new DateTimeImmutable();
        $byStream = [];

        /** @var PendingEvent $pending */
        foreach ($this->pendingEvents as $pending) {
            $key = $pending->streamId()->hex();
            $byStream[$key][] = $pending->recordedWith($scope->causationId(), $scope->correlationId(), $occurredAt);
        }

        return $byStream;
    }

    /**
     * @param array<string, list<RecordedEvent>> $byStream
     */
    private function persistEvents(array $byStream): void
    {
        foreach ($byStream as $recorded) {
            assert($recorded !== [], 'each stream group must have at least one event');
            $first = $recorded[0];
            $v = $first->version();
            $expected = $v->equalTo(StreamVersion::start()) ? new EmptyStream() : new ExactlyAt($v);

            $this->eventStream->append($first->streamId(), $expected, ...$recorded);
        }
    }

    /**
     * @param array<string, list<RecordedEvent>> $byStream
     */
    private function publishEvents(array $byStream): void
    {
        foreach ($byStream as $recorded) {
            foreach ($recorded as $event) {
                $this->publishedEvents->publish(PublishedEvent::fromRecorded($event));
            }
        }
    }

    /**
     * @return list<CommandMessage>
     */
    private function buildCommandsToPersist(MessageScope $scope): array
    {
        $issuedAt = new DateTimeImmutable();
        $messages = [];

        /** @var PendingCommand $pending */
        foreach ($this->pendingCommands as $pending) {
            $messages[] = $pending->issuedWith($scope->causationId(), $scope->correlationId(), $issuedAt);
        }

        return $messages;
    }

    /**
     * @param list<CommandMessage> $messages
     */
    private function persistCommands(array $messages): void
    {
        foreach ($messages as $message) {
            $message->appendInto($this->issuedCommands);
        }
    }

    /**
     * @param list<CommandMessage> $messages
     */
    private function publishCommands(array $messages): void
    {
        foreach ($messages as $message) {
            $message->publishTo($this->commandOutbox);
        }
    }
}
