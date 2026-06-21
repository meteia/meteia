<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use function count;

/**
 * A single page of recorded events from an aggregate's stream, paired with an
 * opaque cursor for lazily loading the next page.
 *
 * When {@see EventPage::nextCursor()} is null the page is the last one; there
 * are no further events to read.
 */
final readonly class EventPage
{
    public function __construct(
        private RecordedEvents $events,
        private ?StreamCursor $nextCursor,
    ) {}

    public function events(): RecordedEvents
    {
        return $this->events;
    }

    public function nextCursor(): ?StreamCursor
    {
        return $this->nextCursor;
    }

    public function hasMore(): bool
    {
        return $this->nextCursor !== null;
    }

    public function isEmpty(): bool
    {
        return count($this->events) === 0;
    }
}
