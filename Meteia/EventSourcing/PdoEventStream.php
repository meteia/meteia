<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\ExpectedVersion;
use Meteia\EventSourcing\Contracts\FromVersion;
use Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure;
use Meteia\MessageStreams\Exceptions\FailedToAppendMessage;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use PDOException;
use ReflectionClass;
use stdClass;

use function assert;
use function is_int;
use function is_string;

class PdoEventStream implements EventStream
{
    private const int SNAPSHOT_REPLAY_MS_THRESHOLD = 15;
    private const int SNAPSHOT_REPLAY_COUNT_THRESHOLD = 25;
    private const string UNIQUE_VIOLATION_SQLSTATE = '23000';

    /** @var array<class-string<EventSourced>, string> */
    private array $aggregateHashes = [];

    public function __construct(
        private readonly ExtendedPdoInterface $db,
        private readonly MessageSerializer $messageSerializer,
        private readonly Timings $timings,
    ) {}

    #[Override]
    public function append(StreamId $streamId, ExpectedVersion $expected, RecordedEvent ...$events): void
    {
        if ($events === []) {
            return;
        }

        $expected->assertCompatibleWith($this->observedVersion($streamId));

        foreach ($events as $event) {
            $this->insertEvent($streamId, $event);
        }
    }

    #[Override]
    public function read(StreamId $streamId, FromVersion $from = new FromFirst()): RecordedEvents
    {
        $rows = $this->db->fetchObjects('
                SELECT aggregate_root_id, aggregate_sequence, event, causation_id, correlation_id, created
                FROM domain_events
                WHERE aggregate_root_id = :aggregateRootId
                  AND aggregate_sequence > :lowerBound
                ORDER BY aggregate_sequence;
            ', [
            'aggregateRootId' => $streamId->bytes(),
            'lowerBound' => $from->lowerBoundExclusive(),
        ]);

        return new RecordedEvents(array_map(fn(stdClass $row): RecordedEvent => $this->hydrate(
            $streamId,
            $row,
        ), $rows));
    }

    #[Override]
    public function page(
        StreamId $streamId,
        FromVersion $from = new FromFirst(),
        int $limit = EventStream::DEFAULT_PAGE_SIZE,
    ): EventPage {
        assert($limit >= 1, 'page limit must be at least 1');

        // Fetch one extra row to detect whether a further page exists without a second query.
        $rows = $this->db->fetchObjects('
                SELECT aggregate_root_id, aggregate_sequence, event, causation_id, correlation_id, created
                FROM domain_events
                WHERE aggregate_root_id = :aggregateRootId
                  AND aggregate_sequence > :lowerBound
                ORDER BY aggregate_sequence
                LIMIT :limit;
            ', [
            'aggregateRootId' => $streamId->bytes(),
            'lowerBound' => $from->lowerBoundExclusive(),
            'limit' => $limit + 1,
        ]);

        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        $events = new RecordedEvents(array_map(fn(stdClass $row): RecordedEvent => $this->hydrate(
            $streamId,
            $row,
        ), $rows));

        $nextCursor = null;
        if ($hasMore) {
            $last = $events[count($events) - 1];
            assert($last instanceof RecordedEvent, 'a non-empty page always has a final recorded event');
            $nextCursor = StreamCursor::after($last->version());
        }

        return new EventPage($events, $nextCursor);
    }

    #[Override]
    public function replay(StreamId $streamId, EventSourced $target): EventSourced
    {
        [$target, $fromVersion] = $this->loadLatestSnapshot($streamId, $target);

        $replayStart = microtime(true);
        $count = 0;
        $lastSequence = -1;
        foreach ($this->read($streamId, $fromVersion) as $recorded) {
            assert($recorded instanceof RecordedEvent, 'RecordedEvents only yields RecordedEvent values');
            $recorded->applyTo($target);
            $lastSequence = $recorded->version()->asInt();
            ++$count;
        }
        $replayDeltaMs = (microtime(true) - $replayStart) * 1000;
        $this->timings->add($target::class . '.replay', $replayDeltaMs);
        $this->timings->add($target::class . '.replayCount', (float) $count);

        if ($this->shouldSnapshot($replayDeltaMs, $count, $lastSequence)) {
            $this->createSnapshot($streamId, $lastSequence, $target);
        }

        return $target;
    }

    private function insertEvent(StreamId $streamId, RecordedEvent $event): void
    {
        try {
            $success = $this->db->fetchAffected('
                    INSERT INTO domain_events
                        (aggregate_root_id, aggregate_sequence, event_type_id, event, causation_id, correlation_id)
                    VALUES
                        (:aggregateRootId, :aggregateSequence, :eventTypeId, :event, :causationId, :correlationId);
                ', [
                'aggregateRootId' => $streamId->bytes(),
                'aggregateSequence' => $event->version()->asInt(),
                'eventTypeId' => $event->eventTypeId()->bytes(),
                'event' => $this->messageSerializer->serialize($event->event()),
                'causationId' => $event->causedBy()->bytes(),
                'correlationId' => $event->correlatedTo()->bytes(),
            ]);
        } catch (PDOException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                throw new OptimisticConcurrencyFailure($event->version(), $this->observedVersion($streamId));
            }

            throw $exception;
        }
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }

    private function observedVersion(StreamId $streamId): StreamVersion
    {
        $row = $this->db->fetchObject('
                SELECT MAX(aggregate_sequence) AS max_seq
                FROM domain_events
                WHERE aggregate_root_id = :aggregateRootId
            ', ['aggregateRootId' => $streamId->bytes()]);

        if ($row === false) {
            return StreamVersion::start();
        }
        assert($row instanceof stdClass, 'fetchObject defaults to hydrating stdClass rows');
        $max = self::nullableNumericColumn($row->max_seq ?? null, 'MAX(aggregate_sequence)');
        if ($max === null) {
            return StreamVersion::start();
        }

        return new StreamVersion($max + 1);
    }

    private function isUniqueConstraintViolation(PDOException $exception): bool
    {
        return (
            $exception->getCode() === self::UNIQUE_VIOLATION_SQLSTATE
            || ($exception->errorInfo[0] ?? '') === self::UNIQUE_VIOLATION_SQLSTATE
        );
    }

    private function hydrate(StreamId $streamId, stdClass $row): RecordedEvent
    {
        $eventRaw = self::stringColumn($row->event ?? null, '`event`');
        $causationIdRaw = self::stringColumn($row->causation_id ?? null, '`causation_id`');
        $correlationIdRaw = self::stringColumn($row->correlation_id ?? null, '`correlation_id`');
        $createdRaw = self::stringColumn($row->created ?? null, '`created`');
        $sequenceRaw = self::numericColumn($row->aggregate_sequence ?? null, '`aggregate_sequence`');

        $pending = new PendingEvent(
            $streamId,
            new StreamVersion($sequenceRaw),
            $this->unserializeAs($eventRaw, DomainEvent::class),
        );

        return new RecordedEvent(
            $pending,
            new CausationId($causationIdRaw),
            new CorrelationId($correlationIdRaw),
            new DateTimeImmutable($createdRaw),
        );
    }

    /**
     * @return array{0: EventSourced, 1: FromVersion}
     */
    private function loadLatestSnapshot(StreamId $streamId, EventSourced $target): array
    {
        $snapshotRow = $this->db->fetchObject('
                SELECT snapshot, aggregate_sequence
                FROM domain_event_snapshots
                WHERE aggregate_root_id = :aggregateRootId AND aggregate_hash = :aggregateHash
                ORDER BY aggregate_sequence DESC
                LIMIT 1
            ', [
            'aggregateRootId' => $streamId->bytes(),
            'aggregateHash' => $this->aggregateHash($target),
        ]);

        if ($snapshotRow === false) {
            return [$target, new FromFirst()];
        }
        assert($snapshotRow instanceof stdClass, 'fetchObject defaults to hydrating stdClass rows');

        $snapshotData = self::stringColumn($snapshotRow->snapshot ?? null, '`snapshot`');
        $sequence = self::numericColumn($snapshotRow->aggregate_sequence ?? null, '`aggregate_sequence`');

        return [
            $this->unserializeAs($snapshotData, EventSourced::class),
            new FromAfter(new StreamVersion($sequence)),
        ];
    }

    private function aggregateHash(EventSourced $target): string
    {
        $cached = $this->aggregateHashes[$target::class] ?? null;
        if ($cached !== null) {
            return $cached;
        }

        $reflection = new ReflectionClass($target);
        $fileName = $reflection->getFileName();
        assert($fileName !== false, 'aggregates always live in a user-defined file');
        $fileHash = hash_file('sha256', $fileName, true);
        assert($fileHash !== false, 'hashing the aggregate source file must succeed');

        $hash = substr($fileHash, 0, 16);
        $this->aggregateHashes[$target::class] = $hash;

        return $hash;
    }

    private function shouldSnapshot(float $replayDeltaMs, int $count, int $lastSequence): bool
    {
        return (
            $replayDeltaMs > self::SNAPSHOT_REPLAY_MS_THRESHOLD
            && $count > self::SNAPSHOT_REPLAY_COUNT_THRESHOLD
            && $lastSequence >= 0
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $expected
     *
     * @return T
     */
    private function unserializeAs(string $raw, string $expected): object
    {
        return self::expectInstance($this->messageSerializer->unserialize($raw), $expected);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $expected
     *
     * @return T
     */
    private static function expectInstance(mixed $value, string $expected): object
    {
        assert(is_object($value) && is_a($value, $expected), 'expected instance of ' . $expected);

        return $value;
    }

    private static function stringColumn(mixed $value, string $context): string
    {
        assert(is_string($value), $context . ' column must be a string');

        return $value;
    }

    private static function numericColumn(mixed $value, string $context): int
    {
        assert(is_int($value) || is_string($value), $context . ' column must be int or numeric string');

        return (int) $value;
    }

    private static function nullableNumericColumn(mixed $value, string $context): ?int
    {
        if ($value === null) {
            return null;
        }

        return self::numericColumn($value, $context);
    }

    private function createSnapshot(StreamId $streamId, int $aggregateSequence, EventSourced $target): void
    {
        $this->timings->add($target::class . '.snapshotUpdate', 1);
        $success = $this->db->fetchAffected('
                REPLACE INTO domain_event_snapshots (aggregate_root_id, aggregate_sequence, aggregate_hash, snapshot)
                VALUES (:aggregateRootId, :aggregateSequence, :aggregateHash, :snapshot)
            ', [
            'aggregateRootId' => $streamId->bytes(),
            'aggregateSequence' => $aggregateSequence,
            'aggregateHash' => $this->aggregateHash($target),
            'snapshot' => $this->messageSerializer->serialize($target),
        ]);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }
}
