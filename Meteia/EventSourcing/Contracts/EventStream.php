<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Contracts;

use Meteia\EventSourcing\FromFirst;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\RecordedEvents;
use Meteia\EventSourcing\StreamId;

interface EventStream
{
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
     * Replay the stream onto an EventSourced aggregate, returning the rehydrated target.
     */
    public function replay(StreamId $streamId, EventSourced $target): EventSourced;
}
