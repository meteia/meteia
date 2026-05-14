<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\UniqueId;
use Override;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * @internal
 */
final class PdoEventStreamTest extends TestCase
{
    public function testAppendingAndReplayingPreservesEvents(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $event = new SomethingRecorded();

        $stream->append($streamId, new EmptyStream(), $this->record($streamId, 0, $event));

        $target = new CountingTarget();
        $stream->replay($streamId, $target);
        PdoEventStreamTest::assertSame(1, $target->count);
    }

    public function testReadReturnsRecordedEventsWithIdsIntact(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $causation = CausationId::random();
        $correlation = CorrelationId::random();
        $pending = new PendingEvent($streamId, new StreamVersion(0), new SomethingRecorded());
        $stream->append(
            $streamId,
            new AnyVersion(),
            new RecordedEvent($pending, $causation, $correlation, new DateTimeImmutable()),
        );

        $events = $stream->read($streamId);
        PdoEventStreamTest::assertCount(1, $events);
        $first = $events[0];
        assert($first instanceof RecordedEvent);
        PdoEventStreamTest::assertSame((string) $causation, (string) $first->causedBy());
        PdoEventStreamTest::assertSame((string) $correlation, (string) $first->correlatedTo());
    }

    public function testEmptyStreamRejectsNonZeroVersion(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $stream->append($streamId, new EmptyStream(), $this->record($streamId, 0, new SomethingRecorded()));

        $this->expectException(OptimisticConcurrencyFailure::class);
        $stream->append($streamId, new EmptyStream(), $this->record($streamId, 1, new SomethingRecorded()));
    }

    public function testExactlyAtMismatchRaisesConcurrencyFailure(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $stream->append($streamId, new AnyVersion(), $this->record($streamId, 0, new SomethingRecorded()));

        $this->expectException(OptimisticConcurrencyFailure::class);
        $stream->append(
            $streamId,
            new ExactlyAt(new StreamVersion(5)),
            $this->record($streamId, 1, new SomethingRecorded()),
        );
    }

    public function testExactlyAtMatchAccepts(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $stream->append($streamId, new AnyVersion(), $this->record($streamId, 0, new SomethingRecorded()));

        $stream->append(
            $streamId,
            new ExactlyAt(new StreamVersion(1)),
            $this->record($streamId, 1, new SomethingRecorded()),
        );

        PdoEventStreamTest::assertCount(2, $stream->read($streamId));
    }

    public function testReadAfterReturnsLaterEventsOnly(): void
    {
        $stream = $this->stream();
        $streamId = StreamId::random();
        $stream->append(
            $streamId,
            new AnyVersion(),
            $this->record($streamId, 0, new SomethingRecorded()),
            $this->record($streamId, 1, new SomethingRecorded()),
            $this->record($streamId, 2, new SomethingRecorded()),
        );

        $tail = $stream->read($streamId, new FromAfter(new StreamVersion(0)));
        PdoEventStreamTest::assertCount(2, $tail);
    }

    private function record(StreamId $streamId, int $version, DomainEvent $event): RecordedEvent
    {
        $pending = new PendingEvent($streamId, new StreamVersion($version), $event);

        return new RecordedEvent($pending, CausationId::random(), CorrelationId::random(), new DateTimeImmutable());
    }

    private function stream(): PdoEventStream
    {
        return new PdoEventStream($this->bootstrappedDatabase(), $this->serializer(), new Timings());
    }

    private function serializer(): MessageSerializer
    {
        return new class extends MessageSerializer {
            #[Override]
            public function serialize(mixed $value): string
            {
                return base64_encode(serialize($value));
            }

            #[Override]
            public function unserialize(string $value): mixed
            {
                $decoded = base64_decode($value, true);
                assert($decoded !== false);

                return unserialize($decoded, ['allowed_classes' => true]);
            }
        };
    }

    private function bootstrappedDatabase(): ExtendedPdoInterface
    {
        $db = new ExtendedPdo('sqlite::memory:');
        $db->exec('
            CREATE TABLE domain_events (
                id                 INTEGER PRIMARY KEY AUTOINCREMENT,
                aggregate_root_id  BLOB NOT NULL,
                aggregate_sequence INTEGER NOT NULL,
                event_type_id      BLOB NOT NULL,
                event              TEXT NOT NULL,
                created            TEXT DEFAULT CURRENT_TIMESTAMP NOT NULL,
                correlation_id     BLOB NOT NULL,
                causation_id       BLOB NOT NULL,
                UNIQUE (aggregate_root_id, aggregate_sequence)
            );
        ');
        $db->exec('
            CREATE TABLE domain_event_snapshots (
                aggregate_root_id  BLOB NOT NULL,
                aggregate_sequence INTEGER NOT NULL,
                aggregate_hash     BLOB NOT NULL,
                snapshot           TEXT NOT NULL,
                created            TEXT DEFAULT CURRENT_TIMESTAMP NOT NULL,
                UNIQUE (aggregate_root_id)
            );
        ');

        return $db;
    }
}

/**
 * @internal
 */
final readonly class SomethingRecorded implements DomainEvent
{
    #[Override]
    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::random();
    }
}

/**
 * @internal
 */
final class CountingTarget implements EventSourced
{
    public int $count = 0;

    public int $sequence = -1;

    #[Override]
    public function commitInto(UnitOfWorkContext $unitOfWorkContext): void {}

    #[Override]
    public function handleEventMessage(UniqueId $streamId, DomainEvent $event, int $eventSequence): void
    {
        ++$this->count;
        $this->sequence = $eventSequence;
    }

    #[Override]
    public function exists(): bool
    {
        return $this->count > 0;
    }
}
