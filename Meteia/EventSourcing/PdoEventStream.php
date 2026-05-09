<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdoInterface;
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

class PdoEventStream implements EventStream
{
    /** @var array<class-string, string> */
    private array $aggregateHashes = [];

    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
        private Timings $timings,
    ) {}

    #[\Override]
    public function append(StreamId $streamId, ExpectedVersion $expected, RecordedEvent ...$events): void
    {
        if ($events === []) {
            return;
        }

        $observed = $this->observedVersion($streamId);
        $expected->assertCompatibleWith($observed);

        $insert = '
            INSERT INTO domain_events (aggregate_root_id, aggregate_sequence, event_type_id, event, causation_id, correlation_id)
            VALUES (:aggregateRootId, :aggregateSequence, :eventTypeId, :event, :causationId, :correlationId);
        ';
        foreach ($events as $event) {
            try {
                $success = $this->db->fetchAffected($insert, [
                    'aggregateRootId' => $streamId->bytes(),
                    'aggregateSequence' => $event->version()->asInt(),
                    'eventTypeId' => $event->eventTypeId()->bytes(),
                    'event' => $this->messageSerializer->serialize($event->event()),
                    'causationId' => $event->causedBy()->bytes(),
                    'correlationId' => $event->correlatedTo()->bytes(),
                ]);
            } catch (\PDOException $exception) {
                if ($this->isUniqueConstraintViolation($exception)) {
                    throw new OptimisticConcurrencyFailure($event->version(), $this->observedVersion($streamId));
                }

                throw $exception;
            }
            if (!$success) {
                throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
            }
        }
    }

    #[\Override]
    public function read(StreamId $streamId, FromVersion $from = new FromFirst()): RecordedEvents
    {
        $rows = $this->db->fetchObjects('
            SELECT aggregate_root_id, aggregate_sequence, event, causation_id, correlation_id, created
            FROM domain_events
            WHERE aggregate_root_id = :aggregateRootId
              AND aggregate_sequence > :lowerBound
            ORDER BY aggregate_sequence ASC;
        ', [
            'aggregateRootId' => $streamId->bytes(),
            'lowerBound' => $from->lowerBoundExclusive(),
        ]);

        return new RecordedEvents(array_map(fn(\stdClass $row): RecordedEvent => $this->hydrate(
            $streamId,
            $row,
        ), $rows));
    }

    #[\Override]
    public function replay(StreamId $streamId, EventSourced $target): EventSourced
    {
        return $this->loadLatestSnapshot($streamId, $target);
    }

    private function observedVersion(StreamId $streamId): StreamVersion
    {
        $row = $this->db->fetchObject('
            SELECT MAX(aggregate_sequence) AS max_seq
            FROM domain_events
            WHERE aggregate_root_id = :aggregateRootId
        ', ['aggregateRootId' => $streamId->bytes()]);

        $max = $row->max_seq;
        if ($max === null) {
            return StreamVersion::start();
        }

        return new StreamVersion((int) $max + 1);
    }

    private function isUniqueConstraintViolation(\PDOException $exception): bool
    {
        return $exception->getCode() === '23000' || ($exception->errorInfo[0] ?? '') === '23000';
    }

    private function hydrate(StreamId $streamId, \stdClass $row): RecordedEvent
    {
        /** @var DomainEvent $event */
        $event = $this->messageSerializer->unserialize($row->event);
        $pending = new PendingEvent($streamId, new StreamVersion((int) $row->aggregate_sequence), $event);

        return new RecordedEvent(
            $pending,
            new CausationId($row->causation_id),
            new CorrelationId($row->correlation_id),
            new \DateTimeImmutable((string) $row->created),
        );
    }

    private function loadLatestSnapshot(StreamId $streamId, EventSourced $target): EventSourced
    {
        if (!isset($this->aggregateHashes[$target::class])) {
            $rc = new \ReflectionClass($target);
            $hash = substr(hash_file('sha256', $rc->getFileName(), true), 0, 16);
            $this->aggregateHashes[$target::class] = $hash;
        }

        $snapshotRow = $this->db->fetchObject('
            SELECT snapshot, aggregate_sequence
            FROM domain_event_snapshots
            WHERE aggregate_root_id = :aggregateRootId AND aggregate_hash = :aggregateHash
            ORDER BY aggregate_sequence DESC
            LIMIT 1
        ', [
            'aggregateRootId' => $streamId->bytes(),
            'aggregateHash' => $this->aggregateHashes[$target::class],
        ]);

        $fromVersion = new FromFirst();
        if ($snapshotRow) {
            $target = $this->messageSerializer->unserialize($snapshotRow->snapshot);
            $fromVersion = new FromAfter(new StreamVersion((int) $snapshotRow->aggregate_sequence));
        }

        $events = $this->read($streamId, $fromVersion);

        $replayStart = microtime(true);
        $count = 0;
        $lastSequence = -1;
        foreach ($events as $recorded) {
            $recorded->applyTo($target);
            $lastSequence = $recorded->version()->asInt();
            ++$count;
        }
        $replayDelta = (microtime(true) - $replayStart) * 1000;
        $this->timings->add($target::class . '.replay', $replayDelta);
        $this->timings->add($target::class . '.replayCount', $count);

        if ($replayDelta > 15 && $count > 25 && $lastSequence >= 0) {
            $this->createSnapshot($streamId, $lastSequence, $target);
        }

        return $target;
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
            'aggregateHash' => $this->aggregateHashes[$target::class],
            'snapshot' => $this->messageSerializer->serialize($target),
        ]);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }
}
