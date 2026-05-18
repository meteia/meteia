<?php

declare(strict_types=1);

namespace Meteia\Events\Database;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\Events\EventOutbox;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\ValueObjects\Identity\MessageScope;
use Override;

final readonly class PdoEventOutbox implements EventOutbox
{
    public function __construct(
        private ExtendedPdoInterface $db,
    ) {}

    #[Override]
    public function record(MessageScope $scope, array $eventGroups): void
    {
        if ($eventGroups === []) {
            return;
        }

        $rows = [];

        foreach ($eventGroups as [$streamId, $recordedEvents]) {
            /** @var StreamId $streamId */
            /** @var list<RecordedEvent> $recordedEvents */
            foreach ($recordedEvents as $event) {
                $rows[] = [
                    'aggregate_root_id' => $streamId->bytes(),
                    'aggregate_sequence' => $event->version()->asInt(),
                ];
            }
        }

        if ($rows === []) {
            return;
        }

        // Use INSERT IGNORE so that replays or duplicate recordings are safe.
        $this->db->perform('
            INSERT IGNORE INTO event_publications (aggregate_root_id, aggregate_sequence)
            VALUES ' . implode(', ', array_fill(0, count($rows), '(?, ?)')),
            array_merge(...array_map(static fn(array $r): array => [$r['aggregate_root_id'], $r['aggregate_sequence']], $rows))
        );
    }
}
