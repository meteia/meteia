<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Commands\CommandOutbox;
use Meteia\DependencyInjection\Container;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Events\EventOutbox;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\EmptyStream;
use Meteia\EventSourcing\ExactlyAt;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Throwable;

/**
 * Collects domain events and commands during a request and flushes them
 * after the HTTP response has been sent. Infrastructure dependencies
 * (event store, outboxes) are resolved lazily via the
 * container so a request with nothing buffered — for example a GraphQL
 * read query — never opens transport connections.
 */
/**
 * @mago-expect analyze:kan-defect -- UoW atomicity (tx + persist-then-publish + proper ExpectedVersion) intentionally increases control-flow; correctness > metric threshold
 */
final class DeferredUnitOfWork implements UnitOfWork
{
    private PendingEvents $pendingEvents;

    private PendingCommands $pendingCommands;

    public function __construct(
        private readonly Container $container,
        private readonly ?PublishedEvents $publishedEvents = null,
        private readonly ?CommandOutbox $commandOutbox = null,
        private readonly ?EventOutbox $eventOutbox = null,
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
        if ($this->pendingEvents->count() === 0 && $this->pendingCommands->count() === 0) {
            return;
        }

        try {
            $this->flush($scope);
        } finally {
            $this->pendingEvents = new PendingEvents();
            $this->pendingCommands = new PendingCommands();
        }
    }

    private function flush(MessageScope $scope): void
    {
        // Build the data to persist (and later publish) before opening a transaction.
        $eventsToPersistAndPublish = $this->buildEventsToPersist($scope);
        $commandsToPersistAndPublish = $this->buildCommandsToPersist($scope);

        if ($eventsToPersistAndPublish === [] && $commandsToPersistAndPublish === []) {
            return;
        }

        $this->runInTransaction(function () use ($scope, $eventsToPersistAndPublish, $commandsToPersistAndPublish): void {
            $this->persistEvents($eventsToPersistAndPublish);
            $this->persistCommands($commandsToPersistAndPublish);

            $this->afterSuccessfulPersist($scope, $eventsToPersistAndPublish, $commandsToPersistAndPublish);
        });

        // Only publish *after* the transaction has committed. DB is the source of truth.
        $this->publishEventsAfterCommit($eventsToPersistAndPublish);
        $this->publishCommandsAfterCommit($commandsToPersistAndPublish);
    }

    protected function runInTransaction(callable $work): void
    {
        /** @var ExtendedPdoInterface $db */
        $db = $this->container->get(ExtendedPdoInterface::class);

        $db->beginTransaction();

        try {
            $work();
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Hook called inside the active transaction, after all domain events and
     * issued commands have been durably written, but before the transaction
     * is committed.
     *
     * This is the correct place to record additional durable work that must
     * succeed or fail atomically with the domain facts (e.g. transactional
     * outbox rows for bus delivery).
     *
     * @param array<string, array{StreamId, list<RecordedEvent>}> $eventGroups
     * @param list<CommandMessage> $commandMessages
     */
    protected function afterSuccessfulPersist(
        MessageScope $scope,
        array $eventGroups,
        array $_commandMessages
    ): void {
        $this->eventOutbox?->record($scope, $eventGroups);
    }

    /**
     * @return array<string, array{StreamId, list<RecordedEvent>}>
     */
    private function buildEventsToPersist(MessageScope $scope): array
    {
        if ($this->pendingEvents->count() === 0) {
            return [];
        }

        $occurredAt = new DateTimeImmutable();
        $byStream = [];

        /** @var PendingEvent $pending */
        foreach ($this->pendingEvents as $pending) {
            $streamId = $pending->streamId();
            $key = $streamId->hex();
            $byStream[$key] ??= [$streamId, []];
            $byStream[$key][1][] = $pending->recordedWith($scope->causationId(), $scope->correlationId(), $occurredAt);
        }

        return $byStream;
    }

    /**
     * @param array<string, array{StreamId, list<RecordedEvent>}> $byStream
     */
    private function persistEvents(array $byStream): void
    {
        if ($byStream === []) {
            return;
        }

        /** @var EventStream $eventStream */
        $eventStream = $this->container->get(EventStream::class);

        foreach ($byStream as [$streamId, $recorded]) {
            assert($recorded !== [], 'each stream group must have at least one event');
            $first = $recorded[0];
            $v = $first->version();
            $expected = $v->equalTo(StreamVersion::start()) ? new EmptyStream() : new ExactlyAt($v);

            $eventStream->append($streamId, $expected, ...$recorded);
        }
    }

    /**
     * @param array<string, array{StreamId, list<RecordedEvent>}> $byStream
     */
    protected function publishEventsAfterCommit(array $byStream): void
    {
        if ($byStream === []) {
            return;
        }

        /** @var PublishedEvents $publishedEvents */
        $publishedEvents = $this->publishedEvents ?? $this->container->get(PublishedEvents::class);

        foreach ($byStream as [, $recorded]) {
            foreach ($recorded as $event) {
                try {
                    $publishedEvents->publish(PublishedEvent::fromRecorded($event));
                } catch (Throwable) {
                    // @mago-expect lint:no-empty-catch-clause -- outbox publish is best-effort side effect for eventual reactors/sinks; pdo append (committed) is the source of truth.
                    // A durable "pending event publications" / transactional outbox table + dedicated publisher worker
                    // can be added later if we need guaranteed delivery to the bus even across RabbitMQ outages
                    // without requiring full stream replay.
                }
            }
        }
    }

    /**
     * @return list<CommandMessage>
     */
    private function buildCommandsToPersist(MessageScope $scope): array
    {
        if ($this->pendingCommands->count() === 0) {
            return [];
        }

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
        if ($messages === []) {
            return;
        }

        /** @var IssuedCommands $issuedCommands */
        $issuedCommands = $this->container->get(IssuedCommands::class);

        foreach ($messages as $message) {
            $message->appendInto($issuedCommands);
        }
    }

    /**
     * @param list<CommandMessage> $messages
     */
    protected function publishCommandsAfterCommit(array $messages): void
    {
        if ($messages === []) {
            return;
        }

        /** @var CommandOutbox $commandOutbox */
        $commandOutbox = $this->commandOutbox ?? $this->container->get(CommandOutbox::class);

        foreach ($messages as $message) {
            try {
                $message->publishTo($commandOutbox);
            } catch (Throwable) {
                // @mago-expect lint:no-empty-catch-clause -- outbox publish is best-effort side effect.
                // Same durable outbox consideration as events applies here for commands.
            }
        }
    }
}
