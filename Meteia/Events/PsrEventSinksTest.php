<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Fixtures\EventSinks\PsrConvention\FixtureOccurred\RecordFixture;
use Meteia\PsrConvention\Events\FixtureOccurred;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PsrEventSinksTest extends TestCase
{
    public function testMapsEventToSinksByPsrConvention(): void
    {
        $events = new PsrEvents(new ApplicationPath('.'), new ApplicationNamespace('Meteia'));
        $sinks = new PsrEventSinks(new ApplicationPath('.'), new ApplicationNamespace('Meteia'), $events);
        $map = iterator_to_array($sinks);

        static::assertArrayHasKey(FixtureOccurred::class, $map);
        $sinkClasses = $map[FixtureOccurred::class] ?? [];
        static::assertContains(RecordFixture::class, $sinkClasses);
    }
}
