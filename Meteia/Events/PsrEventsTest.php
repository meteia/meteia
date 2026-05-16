<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\PsrConvention\Events\FixtureOccurred;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PsrEventsTest extends TestCase
{
    public function testDiscoversDomainEventsByPsrConvention(): void
    {
        $events = new PsrEvents(new ApplicationPath('.'), new ApplicationNamespace('Meteia'));

        static::assertContains(FixtureOccurred::class, iterator_to_array($events));
    }
}
