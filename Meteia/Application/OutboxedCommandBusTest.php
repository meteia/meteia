<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Commands\Command as TransportCommand;
use Meteia\Commands\CommandOutbox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OutboxedCommandBusTest extends TestCase
{
    public function testTransportCommandIsHandedToTheOutbox(): void
    {
        $outbox = new RecordingCommandOutbox();
        $bus = new OutboxedCommandBus($outbox);

        $result = $bus->dispatch(new TransportableCommand());

        static::assertInstanceOf(Accepted::class, $result);
        static::assertCount(1, $outbox->published);
    }

    public function testNonTransportCommandIsRejected(): void
    {
        $outbox = new RecordingCommandOutbox();
        $bus = new OutboxedCommandBus($outbox);

        $result = $bus->dispatch(new ApplicationOnlyCommand());

        static::assertInstanceOf(Rejected::class, $result);
        static::assertCount(0, $outbox->published);
    }
}

/**
 * @internal
 */
final readonly class TransportableCommand implements Command, TransportCommand {}

/**
 * @internal
 */
final readonly class ApplicationOnlyCommand implements Command {}

/**
 * @internal
 */
final class RecordingCommandOutbox implements CommandOutbox
{
    /** @var list<TransportCommand> */
    public array $published = [];

    #[\Override]
    public function publish(TransportCommand $command): void
    {
        $this->published[] = $command;
    }
}
