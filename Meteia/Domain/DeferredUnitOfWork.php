<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Bunny\Client as BunnyClient;
use DateTimeImmutable;
use Meteia\Commands\CommandOutbox;
use Meteia\DependencyInjection\Container;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\Domain\Contracts\UnitOfWork;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\EventSourcing\AnyVersion;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\PendingEvents;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;
use Throwable;

/**
 * Collects domain events and commands during a request and flushes them
 * after the HTTP response has been sent. Infrastructure dependencies
 * (event store, Bunny outboxes) are resolved lazily via the
 * container so a request with nothing buffered — for example a GraphQL
 * read query — never opens a RabbitMQ connection.
 */
final class DeferredUnitOfWork implements UnitOfWork
{
    private PendingEvents $pendingEvents;

    private PendingCommands $pendingCommands;

    public function __construct(
        private readonly Container $container,
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
            $this->flushEvents($scope);
            $this->flushCommands($scope);
        } finally {
            $this->pendingEvents = new PendingEvents();
            $this->pendingCommands = new PendingCommands();
            $this->disconnectBunny();
        }
    }

    private function flushEvents(MessageScope $scope): void
    {
        if ($this->pendingEvents->count() === 0) {
            return;
        }

        /** @var EventStream $eventStream */
        $eventStream = $this->container->get(EventStream::class);
        /** @var PublishedEvents $publishedEvents */
        $publishedEvents = $this->container->get(PublishedEvents::class);

        $occurredAt = new DateTimeImmutable();
        /** @var array<string, array{StreamId, list<RecordedEvent>}> $byStream */
        $byStream = [];
        /** @var PendingEvent $pending */
        foreach ($this->pendingEvents as $pending) {
            $streamId = $pending->streamId();
            $key = $streamId->hex();
            $byStream[$key] ??= [$streamId, []];
            $byStream[$key][1][] = $pending->recordedWith($scope->causationId(), $scope->correlationId(), $occurredAt);
        }

        foreach ($byStream as [$streamId, $recorded]) {
            $eventStream->append($streamId, new AnyVersion(), ...$recorded);
            foreach ($recorded as $event) {
                try {
                    $publishedEvents->publish(PublishedEvent::fromRecorded($event));
                } catch (Throwable) {
                    // @mago-expect lint:no-empty-catch-clause -- outbox publish is best-effort side effect for eventual reactors/sinks; pdo append is the source of truth
                }
            }
        }
    }

    private function flushCommands(MessageScope $scope): void
    {
        if ($this->pendingCommands->count() === 0) {
            return;
        }

        /** @var IssuedCommands $issuedCommands */
        $issuedCommands = $this->container->get(IssuedCommands::class);
        /** @var CommandOutbox $commandOutbox */
        $commandOutbox = $this->container->get(CommandOutbox::class);

        $issuedAt = new DateTimeImmutable();
        /** @var PendingCommand $pending */
        foreach ($this->pendingCommands as $pending) {
            $message = $pending->issuedWith($scope->causationId(), $scope->correlationId(), $issuedAt);
            $message->appendInto($issuedCommands);
            try {
                $message->publishTo($commandOutbox);
            } catch (Throwable) {
                // @mago-expect lint:no-empty-catch-clause -- outbox publish is best-effort side effect
            }
        }
    }

    private function disconnectBunny(): void
    {
        if (!$this->container->has(BunnyClient::class)) {
            return;
        }

        try {
            /** @var BunnyClient $client */
            $client = $this->container->get(BunnyClient::class);
            if ($client->isConnected()) {
                $client->disconnect();
            }

            // @mago-expect lint:no-empty-catch-clause -- disconnecting is best-effort; heartbeat will reap.
        } catch (Throwable) {
        }
    }
}
