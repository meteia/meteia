<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\MessageStreams\Exceptions\FailedToAppendMessage;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use ReflectionClass;

class PdoEventStream implements EventStream
{
    private array $aggregateHashes = [];

    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
        private Timings $timings,
    ) {
    }

    public function append(
        AggregateRootId $aggregateRootId,
        int $aggregateSequence,
        EventTypeId $eventTypeId,
        DomainEvent $event,
        CausationId $causationId,
        CorrelationId $correlationId,
    ): void {
        $query = '
            INSERT INTO domain_events (aggregate_root_id, aggregate_sequence, event_type_id, event, causation_id, correlation_id)
            VALUES (:aggregateRootId, :aggregateSequence, :eventTypeId, :event, :causationId, :correlationId);
        ';
        $bindings = [
            'aggregateRootId' => $aggregateRootId->bytes(),
            'aggregateSequence' => $aggregateSequence,
            'eventTypeId' => $eventTypeId->bytes(),
            'event' => $this->messageSerializer->serialize($event),
            'causationId' => $causationId->bytes(),
            'correlationId' => $correlationId->bytes(),
        ];
        $success = $this->db->fetchAffected($query, $bindings);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }

    public function replay(AggregateRootId $aggregateRootId, EventSourced $target): EventSourced
    {
        return $this->loadLatestSnapshot($aggregateRootId, $target);
    }

    private function loadLatestSnapshot(AggregateRootId $aggregateRootId, EventSourced $target): EventSourced
    {
        // TODO: Use APCu or similar cache? (benchmark first, PHP OpCache might be enough)
        if (!isset($this->aggregateHashes[$target::class])) {
            $rc = new ReflectionClass($target);
            $hash = substr(hash_file('sha256', $rc->getFileName(), true), 0, 16);
            $this->aggregateHashes[$target::class] = $hash;
        }

        // dump($this->aggregateHashes[$target::class]);
        $query = '
            SELECT snapshot, aggregate_sequence
            FROM domain_event_snapshots
            WHERE aggregate_root_id = :aggregateRootId AND aggregate_hash = :aggregateHash
            ORDER BY aggregate_sequence DESC
            LIMIT 1
        ';
        $bindings = [
            'aggregateRootId' => $aggregateRootId->bytes(),
            'aggregateHash' => $this->aggregateHashes[$target::class],
        ];
        $snapshotRow = $this->db->fetchObject($query, $bindings);
        if ($snapshotRow) {
            $target = $this->messageSerializer->unserialize($snapshotRow->snapshot);

            $query = '
                SELECT event, aggregate_sequence, causation_id, correlation_id
                FROM domain_events
                WHERE aggregate_root_id = :aggregateRootId
                AND aggregate_sequence > :aggregateSequence
                ORDER BY aggregate_sequence ASC;
            ';
            $bindings = [
                'aggregateRootId' => $aggregateRootId->bytes(),
                'aggregateSequence' => $snapshotRow->aggregate_sequence,
            ];
        } else {
            $query = '
                SELECT event, aggregate_sequence, causation_id, correlation_id
                FROM domain_events
                WHERE aggregate_root_id = :aggregateRootId
                ORDER BY aggregate_sequence ASC;
            ';
            $bindings = [
                'aggregateRootId' => $aggregateRootId->bytes(),
            ];
        }

        $messageRows = $this->db->fetchObjects($query, $bindings);

        $replayStart = microtime(true);
        foreach ($messageRows as $row) {
            /** @var DomainEvent $event */
            $event = $this->messageSerializer->unserialize($row->event);
            $eventMessage = new EventMessage(
                $aggregateRootId,
                $event,
                (int) $row->aggregate_sequence,
                new CausationId($row->causation_id),
                new CorrelationId($row->correlation_id),
            );
            $eventMessage->applyTo($target);
        }
        $replayDelta = (microtime(true) - $replayStart) * 1000;
        $this->timings->add($target::class . '.replay', $replayDelta);
        $this->timings->add($target::class . '.replayCount', count($messageRows));

        if ($replayDelta > 15 && count($messageRows) > 25) {
            $lastMessageRow = end($messageRows);
            $this->createSnapshot($aggregateRootId, (int) $lastMessageRow->aggregate_sequence, $target);
        }

        return $target;
    }

    private function createSnapshot(AggregateRootId $aggregateRootId, int $aggregateSequence, $target): void
    {
        $this->timings->add($target::class . '.snapshotUpdate', 1);
        $query = '
            REPLACE INTO domain_event_snapshots (aggregate_root_id, aggregate_sequence, aggregate_hash, snapshot)
            VALUES (:aggregateRootId, :aggregateSequence, :aggregateHash, :snapshot)
        ';
        $bindings = [
            'aggregateRootId' => $aggregateRootId->bytes(),
            'aggregateSequence' => $aggregateSequence,
            'aggregateHash' => $this->aggregateHashes[$target::class],
            'snapshot' => $this->messageSerializer->serialize($target),
        ];
        $success = $this->db->fetchAffected($query, $bindings);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }
}
