<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Commands\Fixtures\ExampleOutboxedCommand;
use Meteia\Commands\Fixtures\RecordingCommandOutbox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OutboxedCommandBusTest extends TestCase
{
    public function testCommandIsHandedToTheOutbox(): void
    {
        $outbox = new RecordingCommandOutbox();
        $bus = new OutboxedCommandBus($outbox);

        $bus->dispatch(new ExampleOutboxedCommand());

        static::assertCount(1, $outbox->published);
    }
}
