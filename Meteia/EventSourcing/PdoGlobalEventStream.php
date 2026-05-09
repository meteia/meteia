<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\EventSourcing\Contracts\GlobalEventStream;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Projections\GlobalSequence;
use Meteia\Projections\ProjectableEvent;
use Meteia\Projections\ProjectableEvents;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

final readonly class PdoGlobalEventStream implements GlobalEventStream
{
    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
    ) {}

    #[\Override]
    public function readGlobally(GlobalSequence $after = new GlobalSequence(0)): ProjectableEvents
    {
        $rows = $this->db->fetchObjects('
            SELECT id, aggregate_root_id, aggregate_sequence, event, causation_id, correlation_id, created
            FROM domain_events
            WHERE id > :lowerBound
            ORDER BY id ASC
        ', ['lowerBound' => $after->asInt()]);

        $projectable = array_map(
            fn(\stdClass $row): ProjectableEvent => new ProjectableEvent(
                $this->hydrate($row),
                new GlobalSequence((int) $row->id),
            ),
            $rows,
        );

        return new ProjectableEvents($projectable);
    }

    private function hydrate(\stdClass $row): RecordedEvent
    {
        $streamId = new StreamId($row->aggregate_root_id);
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
}
