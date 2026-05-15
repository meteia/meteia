<?php

declare(strict_types=1);

namespace Meteia\Events;

use IteratorAggregate;
use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Classy\ClassesImplementing;
use Meteia\Classy\MergedClasses;
use Meteia\Classy\PsrClasses;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Override;
use Traversable;

/**
 * @implements IteratorAggregate<array-key, class-string<EventSink>>
 */
final readonly class EventSinks implements IteratorAggregate
{
    public function __construct(
        private ApplicationPath $applicationPath,
        private ApplicationNamespace $applicationNamespace,
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        // Support grouped layout EventSinks/<SourceContext>/<EventName>/<Handler>.php
        // under any reacting context (e.g. Counters/EventSinks/Counters/CounterDecremented/...
        // or future Users/EventSinks/Timers/TimerExpired/...). The .* before EventSinks
        // absorbs the reacting context dir; the .* \.php after absorbs SourceContext + EventName + file.
        $regex = ['.*', 'EventSinks', '.*\.php'];

        yield from new ClassesImplementing(
            new MergedClasses(
                new PsrClasses(new FilesystemPath(__DIR__, '..', '..'), 'Meteia', $regex),
                new PsrClasses($this->applicationPath, (string) $this->applicationNamespace, $regex),
            ),
            EventSink::class,
        );
    }
}
