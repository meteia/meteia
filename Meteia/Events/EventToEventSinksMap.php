<?php

declare(strict_types=1);

namespace Meteia\Events;

use IteratorAggregate;
use Override;
use Traversable;

readonly class EventToEventSinksMap implements IteratorAggregate
{
    public function __construct(
        public Events $events,
        public EventSinks $eventSinks,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        $sinks = iterator_to_array($this->eventSinks);
        foreach ($this->events as $event) {
            $normalizedEvent = $this->normalizedEvent($event);

            yield $event => array_filter(
                $sinks,
                fn(string $sink): bool => $normalizedEvent === $this->normalizedEventSink($sink),
            );
        }
    }

    private function normalizedEvent(string $className): string
    {
        $parts = explode('\\', $className);
        array_splice($parts, 2, 1);

        return implode('.', $parts);
    }

    private function normalizedEventSink(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts);
        array_splice($parts, 1, 2);

        return implode('.', $parts);
    }
}
