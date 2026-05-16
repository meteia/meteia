<?php

declare(strict_types=1);

namespace Meteia\EventSourcing\Fixtures;

use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\ExpectedVersion;
use Meteia\EventSourcing\Contracts\FromVersion;
use Meteia\EventSourcing\FromFirst;
use Meteia\EventSourcing\RecordedEvent;
use Meteia\EventSourcing\RecordedEvents;
use Meteia\EventSourcing\StreamId;
use Override;

/**
 * @internal
 */
final class EmptyEventStream implements EventStream
{
    #[Override]
    public function append(StreamId $streamId, ExpectedVersion $expected, RecordedEvent ...$events): void {}

    #[Override]
    public function read(StreamId $streamId, FromVersion $from = new FromFirst()): RecordedEvents
    {
        return new RecordedEvents();
    }

    #[Override]
    public function replay(StreamId $streamId, EventSourced $target): EventSourced
    {
        return $target;
    }
}
