<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Generator;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\ValueObjects\AggregateRootId;

/**
 * Consumer-facing facade for inspecting an aggregate root's recorded event
 * stream.
 *
 * Inspection is read-only and deliberately separate from the command-side
 * {@see EventSourcedRepository}: it never reconstitutes an aggregate, it just
 * exposes the events as they were recorded. Use it for audit trails, debugging,
 * and admin tooling. Application read models should still be built from
 * dedicated projections rather than scanning history here.
 */
final readonly class AggregateHistory
{
    public function __construct(
        private EventStream $eventStream,
    ) {}

    /**
     * Fetch one page of an aggregate's events.
     *
     * Pass the opaque token from the previous page's {@see EventPage::nextCursor()}
     * to continue; omit it (or pass null) to start from the first event.
     *
     * @param positive-int $limit
     *
     * @throws \InvalidArgumentException when the cursor token is not a valid stream cursor
     */
    public function page(
        AggregateRootId $id,
        ?string $cursor = null,
        int $limit = EventStream::DEFAULT_PAGE_SIZE,
    ): EventPage {
        $from = $cursor === null ? new FromFirst() : StreamCursor::fromToken($cursor);

        return $this->eventStream->page($this->streamId($id), $from, $limit);
    }

    /**
     * Lazily iterate every recorded event for an aggregate, transparently
     * fetching pages on demand so the whole stream is never held in memory at
     * once.
     *
     * @param positive-int $pageSize how many events to load per underlying read
     *
     * @return Generator<int, RecordedEvent>
     */
    public function events(AggregateRootId $id, int $pageSize = EventStream::DEFAULT_PAGE_SIZE): Generator
    {
        $streamId = $this->streamId($id);
        $from = new FromFirst();

        do {
            $page = $this->eventStream->page($streamId, $from, $pageSize);
            foreach ($page->events() as $event) {
                yield $event;
            }
            $next = $page->nextCursor();
            if ($next !== null) {
                $from = $next;
            }
        } while ($next !== null);
    }

    private function streamId(AggregateRootId $id): StreamId
    {
        return new StreamId($id->bytes());
    }
}
