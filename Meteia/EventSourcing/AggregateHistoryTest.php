<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use PHPUnit\Framework\TestCase;

use function assert;
use function iterator_to_array;

/**
 * @internal
 */
final class AggregateHistoryTest extends TestCase
{
    public function testPagesThroughTheStreamUsingOpaqueCursorTokens(): void
    {
        $stream = $this->stream();
        $id = HistoryTestId::random();
        $this->append($stream, $id, 3);

        $history = new AggregateHistory($stream);

        $first = $history->page($id, null, 2);
        AggregateHistoryTest::assertCount(2, $first->events());
        AggregateHistoryTest::assertTrue($first->hasMore());

        $cursorToken = (string) $first->nextCursor();
        $second = $history->page($id, $cursorToken, 2);
        AggregateHistoryTest::assertCount(1, $second->events());
        AggregateHistoryTest::assertFalse($second->hasMore());
    }

    public function testEventsLazilyIteratesTheWholeStreamAcrossPages(): void
    {
        $stream = $this->stream();
        $id = HistoryTestId::random();
        $this->append($stream, $id, 5);

        $history = new AggregateHistory($stream);

        $events = iterator_to_array($history->events($id, 2), false);

        AggregateHistoryTest::assertCount(5, $events);
        foreach ($events as $index => $event) {
            assert($event instanceof RecordedEvent, 'history yields recorded events');
            AggregateHistoryTest::assertSame($index, $event->version()->asInt());
        }
    }

    public function testEventsOnAnEmptyStreamYieldsNothing(): void
    {
        $history = new AggregateHistory($this->stream());

        AggregateHistoryTest::assertCount(0, iterator_to_array($history->events(HistoryTestId::random()), false));
    }

    private function append(PdoEventStream $stream, AggregateRootId $id, int $count): void
    {
        $streamId = new StreamId($id->bytes());
        $events = [];
        for ($version = 0; $version < $count; ++$version) {
            $pending = new PendingEvent($streamId, new StreamVersion($version), new HistoryRecorded());
            $events[] = new RecordedEvent(
                $pending,
                CausationId::random(),
                CorrelationId::random(),
                new DateTimeImmutable(),
            );
        }
        $stream->append($streamId, new AnyVersion(), ...$events);
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
                assert($decoded !== false, 'serialized fixtures are valid base64');

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

        return $db;
    }
}

/**
 * @internal
 */
final readonly class HistoryTestId extends AggregateRootId
{
    #[Override]
    public static function prefix(): string
    {
        return 'hist';
    }
}

/**
 * @internal
 */
final readonly class HistoryRecorded implements DomainEvent
{
    #[Override]
    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::random();
    }
}
