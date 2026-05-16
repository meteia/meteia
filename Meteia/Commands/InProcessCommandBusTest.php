<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Commands\Exceptions\UnknownCommandHandler;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\Commands\Fixtures\RecordingCommandHandler;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InProcessCommandBusTest extends TestCase
{
    public function testDispatchHandsCommandToTheRegisteredEndpoint(): void
    {
        $handler = new RecordingCommandHandler();
        $handlers = new class($handler) implements CommandHandlers {
            public function __construct(
                private readonly CommandHandler $handler,
            ) {}

            #[Override]
            public function handlerFor(Command $command): CommandHandler
            {
                return $this->handler;
            }
        };
        $bus = new InProcessCommandBus($handlers);

        $bus->dispatch(new ExampleCommand());

        static::assertCount(1, $handler->received);
    }

    public function testDispatchPropagatesHandlerResolutionErrors(): void
    {
        $handlers = new class implements CommandHandlers {
            #[Override]
            public function handlerFor(Command $command): CommandHandler
            {
                throw new UnknownCommandHandler($command::class);
            }
        };
        $bus = new InProcessCommandBus($handlers);

        $this->expectException(UnknownCommandHandler::class);
        $bus->dispatch(new ExampleCommand());
    }
}
