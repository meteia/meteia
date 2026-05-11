<?php

declare(strict_types=1);

namespace Meteia\Application;

use Meteia\Application\Exceptions\UnknownCommandEndpoint;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InProcessCommandBusTest extends TestCase
{
    public function testDispatchHandsCommandToTheRegisteredEndpoint(): void
    {
        $endpoint = new RecordingEndpoint();
        $registry = new class($endpoint) implements CommandEndpointRegistry {
            public function __construct(
                private readonly CommandEndpoint $endpoint,
            ) {}

            #[Override]
            public function endpointFor(string $commandClass): CommandEndpoint
            {
                return $this->endpoint;
            }
        };
        $bus = new InProcessCommandBus($registry);

        $result = $bus->dispatch(new ExampleApplicationCommand());

        static::assertInstanceOf(Accepted::class, $result);
        static::assertCount(1, $endpoint->received);
    }

    public function testDispatchPropagatesEndpointResolutionErrors(): void
    {
        $registry = new class implements CommandEndpointRegistry {
            #[Override]
            public function endpointFor(string $commandClass): CommandEndpoint
            {
                throw new UnknownCommandEndpoint($commandClass);
            }
        };
        $bus = new InProcessCommandBus($registry);

        $this->expectException(UnknownCommandEndpoint::class);
        $bus->dispatch(new ExampleApplicationCommand());
    }
}

/**
 * @internal
 */
final readonly class ExampleApplicationCommand implements Command {}

/**
 * @internal
 */
final class RecordingEndpoint implements CommandEndpoint
{
    /** @var list<Command> */
    public array $received = [];

    #[Override]
    public function handle(Command $command): CommandResult
    {
        $this->received[] = $command;

        return new Accepted();
    }
}
