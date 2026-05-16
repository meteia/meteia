<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Traversable;

final readonly class PsrEventSinks implements EventSinks
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
        private Events $events,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        $sinks = iterator_to_array($this->sinkClasses());
        foreach ($this->events as $event) {
            $normalizedEvent = $this->normalizedEvent($event);

            yield $event => array_filter(
                $sinks,
                function (string $sink) use ($normalizedEvent): bool {
                    \assert(is_subclass_of($sink, EventSink::class), 'PSR event sink discovery must return EventSink classes');

                    return $normalizedEvent === $this->normalizedEventSink($sink);
                },
            );
        }
    }

    /**
     * @return iterable<array-key, class-string<EventSink>>
     */
    private function sinkClasses(): iterable
    {
        $regex = ['.*', 'EventSinks', '.*\.php'];

        $sinks = new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            EventSink::class,
        );
        foreach ($sinks as $sink) {
            \assert(is_subclass_of($sink, EventSink::class), 'PSR event sink discovery must return EventSink classes');

            yield $sink;
        }
    }

    /**
     * @param class-string<DomainEvent> $className
     */
    private function normalizedEvent(string $className): string
    {
        $parts = explode('\\', $className);
        array_splice($parts, 2, 1);

        return implode('.', $parts);
    }

    /**
     * @param class-string<EventSink> $className
     */
    private function normalizedEventSink(string $className): string
    {
        $parts = explode('\\', $className);
        array_pop($parts);
        array_splice($parts, 1, 2);

        return implode('.', $parts);
    }
}
