<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\EventSourcing\Contracts\EventStream;
use Meteia\EventSourcing\Contracts\ExpectedVersion;
use Meteia\EventSourcing\Contracts\FromVersion;
use Meteia\EventSourcing\Contracts\GlobalEventStream;
use Meteia\EventSourcing\Exceptions\OptimisticConcurrencyFailure;
use Meteia\Projections\GlobalSequence;
use Meteia\Projections\ProjectableEvent;
use Meteia\Projections\ProjectableEvents;

final class InMemoryEventStream implements EventStream, GlobalEventStream
{
    /** @var array<string, RecordedEvent[]> */
    private array $byStream = [];

    /** @var array<int, array{streamId: StreamId, event: RecordedEvent}> */
    private array $global = [];

    private int $globalCursor = 0;

    #[\Override]
    public function append(StreamId $streamId, ExpectedVersion $expected, RecordedEvent ...$events): void
    {
        if ($events === []) {
            return;
        }

        $key = $streamId->hex();
        $observed = $this->observedVersion($key);
        $expected->assertCompatibleWith($observed);

        foreach ($events as $event) {
            $sequence = $event->version()->asInt();
            foreach ($this->byStream[$key] ?? [] as $existing) {
                if ($existing->version()->asInt() === $sequence) {
                    throw new OptimisticConcurrencyFailure($event->version(), $this->observedVersion($key));
                }
            }
            $this->byStream[$key][] = $event;
            ++$this->globalCursor;
            $this->global[$this->globalCursor] = ['streamId' => $streamId, 'event' => $event];
        }
    }

    #[\Override]
    public function read(StreamId $streamId, FromVersion $from = new FromFirst()): RecordedEvents
    {
        $key = $streamId->hex();
        $events = $this->byStream[$key] ?? [];
        $lower = $from->lowerBoundExclusive();
        $tail = array_values(array_filter(
            $events,
            static fn(RecordedEvent $event): bool => $event->version()->asInt() > $lower,
        ));

        usort(
            $tail,
            static fn(RecordedEvent $a, RecordedEvent $b): int => $a->version()->asInt() <=> $b->version()->asInt(),
        );

        return new RecordedEvents($tail);
    }

    #[\Override]
    public function replay(StreamId $streamId, EventSourced $target): EventSourced
    {
        foreach ($this->read($streamId) as $recorded) {
            $recorded->applyTo($target);
        }

        return $target;
    }

    #[\Override]
    public function readGlobally(GlobalSequence $after = new GlobalSequence(0)): ProjectableEvents
    {
        $projectable = [];
        foreach ($this->global as $position => $entry) {
            if ($position <= $after->asInt()) {
                continue;
            }
            $projectable[] = new ProjectableEvent($entry['event'], new GlobalSequence($position));
        }

        return new ProjectableEvents($projectable);
    }

    private function observedVersion(string $streamKey): StreamVersion
    {
        $events = $this->byStream[$streamKey] ?? [];
        if ($events === []) {
            return StreamVersion::start();
        }

        $max = 0;
        foreach ($events as $event) {
            $sequence = $event->version()->asInt();
            if ($sequence > $max) {
                $max = $sequence;
            }
        }

        return new StreamVersion($max + 1);
    }
}
