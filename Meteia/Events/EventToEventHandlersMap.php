<?php

declare(strict_types=1);

namespace Meteia\Events;

readonly class EventToEventHandlersMap implements \IteratorAggregate
{
    public function __construct(
        public Events $events,
        public EventHandlers $eventHandlers,
    ) {}

    #[\Override]
    public function getIterator(): \Traversable
    {
        $eventHandlers = iterator_to_array($this->eventHandlers);
        foreach ($this->events as $event) {
            $normalizedEvent = $this->normalizedEvent($event);

            yield $event => array_filter(
                $eventHandlers,
                fn(string $eventHandler) => $normalizedEvent === $this->normalizedEventHandler($eventHandler),
            );
        }
    }

    private function normalizedEvent(string $className): string
    {
        $parts = explode('\\', $className);
        array_splice($parts, 2, 1);

        return implode('.', $parts);
    }

    private function normalizedEventHandler(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts);
        array_splice($parts, 1, 2);

        return implode('.', $parts);
    }
}
