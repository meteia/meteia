<?php

declare(strict_types=1);

namespace Meteia\Events;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\EventSourcing\PendingEvent;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Psr\Log\LoggerInterface;
use stdClass;
use Throwable;

/**
 * Drains pending rows from the event publications outbox and attempts
 * reliable delivery via the real PublishedEvents implementation.
 *
 * This provides at-least-once delivery for domain events to the message bus
 * even when the initial publish during the UnitOfWork failed or the broker
 * was temporarily unavailable.
 */
final readonly class OutboxPublisher
{
    private const int MAX_ATTEMPTS = 10;

    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $serializer,
        private PublishedEvents $publishedEvents,
        private LoggerInterface $log,
    ) {}

    /**
     * Process up to $limit pending publications.
     *
     * @return int number of publications successfully published in this run
     */
    public function publishPending(int $limit = 100): int
    {
        $rows = $this->fetchPending($limit);

        $publishedCount = 0;

        foreach ($rows as $row) {
            try {
                $publishedEvent = $this->hydratePublishedEvent($row);

                $this->publishedEvents->publish($publishedEvent);

                $this->markPublished((int) $row->publication_id);

                $publishedCount++;
            } catch (Throwable $exception) {
                $this->recordFailure((int) $row->publication_id, $exception);

                $this->log->warning('Failed to publish event from outbox', [
                    'publication_id' => $row->publication_id,
                    'aggregate_root_id' => bin2hex($row->aggregate_root_id ?? ''),
                    'aggregate_sequence' => $row->aggregate_sequence ?? null,
                    'attempts' => $row->attempts ?? 0,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        if ($publishedCount > 0) {
            $this->log->info('Outbox drain batch completed', [
                'published' => $publishedCount,
                'attempted' => count($rows),
            ]);
        }

        return $publishedCount;
    }

    /**
     * @return list<stdClass>
     */
    private function fetchPending(int $limit): array
    {
        return $this->db->fetchObjects(
            'SELECT
                ep.id as publication_id,
                ep.aggregate_root_id,
                ep.aggregate_sequence,
                ep.attempts,
                de.event,
                de.causation_id,
                de.correlation_id,
                de.created
             FROM event_publications ep
             JOIN domain_events de
               ON de.aggregate_root_id = ep.aggregate_root_id
              AND de.aggregate_sequence = ep.aggregate_sequence
             WHERE ep.status = "pending"
               AND ep.attempts < ?
             ORDER BY ep.id
             LIMIT ?',
            [self::MAX_ATTEMPTS, $limit]
        );
    }

    private function hydratePublishedEvent(stdClass $row): PublishedEvent
    {
        $event = $this->serializer->unserialize($row->event);

        $pending = new PendingEvent(
            new StreamId($row->aggregate_root_id),
            new StreamVersion((int) $row->aggregate_sequence),
            $event
        );

        $recorded = new RecordedEvent(
            $pending,
            new CausationId($row->causation_id),
            new CorrelationId($row->correlation_id),
            new DateTimeImmutable($row->created)
        );

        return PublishedEvent::fromRecorded($recorded);
    }

    private function markPublished(int $publicationId): void
    {
        $this->db->fetchAffected(
            'UPDATE event_publications
             SET status = "published",
                 published_at = NOW(6),
                 last_attempt_at = NOW(6)
             WHERE id = ?',
            [$publicationId]
        );
    }

    private function recordFailure(int $publicationId, Throwable $exception): void
    {
        $this->db->fetchAffected(
            'UPDATE event_publications
             SET status = CASE
                            WHEN attempts + 1 >= ? THEN "dead_lettered"
                            ELSE "failed"
                          END,
                 attempts = attempts + 1,
                 last_attempt_at = NOW(6),
                 last_error = ?
             WHERE id = ?',
            [self::MAX_ATTEMPTS, $exception->getMessage(), $publicationId]
        );
    }
}
