<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 */
final class PsrEventSinksTest extends TestCase
{
    public function testMatchesEventSinksOwnedByBoundedContext(): void
    {
        $events = new PsrEvents(new ApplicationPath('.'), new ApplicationNamespace('Meteia'));
        $eventSinks = new PsrEventSinks(new ApplicationPath('.'), new ApplicationNamespace('Meteia'), $events);

        $event = new ReflectionMethod($eventSinks, 'normalizedEvent');
        $sink = new ReflectionMethod($eventSinks, 'normalizedEventSink');

        static::assertSame(
            $event->invoke($eventSinks, 'Example\Users\Events\UserRegistered'),
            $sink->invoke($eventSinks, 'Example\Users\EventSinks\UserRegistered\UpdateUserProjections'),
        );
    }
}
