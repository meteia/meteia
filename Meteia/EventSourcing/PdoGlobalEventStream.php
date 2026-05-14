<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\Contracts\GlobalEventStream;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Projections\GlobalSequence;
use Meteia\Projections\ProjectableEvent;
use Meteia\Projections\ProjectableEvents;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use stdClass;
use function assert;
use function is_string;

final readonly class PdoGlobalEventStream implements GlobalEventStream
{
    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
    ) {}

    #[Override]
    public function readGlobally(GlobalSequence $after = new GlobalSequence(0)): ProjectableEvents
    {
        $rows = $this->db->fetchObjects('
            SELECT id, aggregate_root_id, aggregate_sequence, event, causation_id, correlation_id, created
            FROM domain_events
            WHERE id > :lowerBound
            ORDER BY id ASC
        ', ['lowerBound' => $after->asInt()]);

        $projectable = array_map(
            fn(stdClass $row): ProjectableEvent => new ProjectableEvent(
                $this->hydrate($row),
                new GlobalSequence((int) $row->id),
            ),
            $rows,
        );

        return new ProjectableEvents($projectable);
    }

    private function hydrate(stdClass $row): RecordedEvent
    {
        $aggregateRootIdRaw = $row->aggregate_root_id;
        $eventRaw = $row->event;
        $causationIdRaw = $row->causation_id;
        $correlationIdRaw = $row->correlation_id;
        assert(
            is_string($aggregateRootIdRaw)
            && is_string($eventRaw)
            && is_string($causationIdRaw)
            && is_string($correlationIdRaw),
        );
        $streamId = new StreamId($aggregateRootIdRaw);
        /** @var DomainEvent $event */
        $event = $this->messageSerializer->unserialize($eventRaw);
        $pending = new PendingEvent($streamId, new StreamVersion((int) $row->aggregate_sequence), $event);

        return new RecordedEvent(
            $pending,
            new CausationId($causationIdRaw),
            new CorrelationId($correlationIdRaw),
            new DateTimeImmutable((string) $row->created),
        );
    }
}
