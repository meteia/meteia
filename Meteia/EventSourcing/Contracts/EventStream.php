<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\EventSourcing\EventPage;
use Meteia\EventSourcing\FromFirst;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\RecordedEvents;
use Meteia\EventSourcing\StreamId;

interface EventStream
{
    /**
     * Default number of events returned by a single {@see EventStream::page()} read.
     */
    public const int DEFAULT_PAGE_SIZE = 100;

    /**
     * Append events to the stream, asserting that the observed version matches the expectation.
     *
     * @throws \Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure on version mismatch
     */
    public function append(StreamId $streamId, ExpectedVersion $expected, RecordedEvent ...$events): void;

    /**
     * Read the recorded events of a stream, optionally starting after a given version.
     */
    public function read(StreamId $streamId, FromVersion $from = new FromFirst()): RecordedEvents;

    /**
     * Read a single page of recorded events in stream order, optionally starting
     * after a given version (for example the cursor from a previous page).
     *
     * The returned {@see EventPage} carries an opaque cursor for fetching the
     * next page, letting consumers walk a long stream lazily instead of loading
     * every event into memory at once.
     *
     * @param positive-int $limit maximum number of events to return in the page
     */
    public function page(
        StreamId $streamId,
        FromVersion $from = new FromFirst(),
        int $limit = self::DEFAULT_PAGE_SIZE,
    ): EventPage;

    /**
     * Replay the stream onto an EventSourced aggregate, returning the rehydrated target.
     */
    public function replay(StreamId $streamId, EventSourced $target): EventSourced;
}
