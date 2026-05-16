<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\Commands\Exceptions\UnknownCommandEndpoint;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\Commands\Fixtures\RecordingCommandEndpoint;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InProcessCommandBusTest extends TestCase
{
    public function testDispatchHandsCommandToTheRegisteredEndpoint(): void
    {
        $endpoint = new RecordingCommandEndpoint();
        $endpoints = new class($endpoint) implements CommandEndpoints {
            public function __construct(
                private readonly CommandEndpoint $endpoint,
            ) {}

            #[Override]
            public function endpointFor(string $commandClass): CommandEndpoint
            {
                return $this->endpoint;
            }
        };
        $bus = new InProcessCommandBus($endpoints);

        $result = $bus->dispatch(new ExampleCommand());

        static::assertInstanceOf(Accepted::class, $result);
        static::assertCount(1, $endpoint->received);
    }

    public function testDispatchPropagatesEndpointResolutionErrors(): void
    {
        $endpoints = new class implements CommandEndpoints {
            #[Override]
            public function endpointFor(string $commandClass): CommandEndpoint
            {
                throw new UnknownCommandEndpoint($commandClass);
            }
        };
        $bus = new InProcessCommandBus($endpoints);

        $this->expectException(UnknownCommandEndpoint::class);
        $bus->dispatch(new ExampleCommand());
    }
}
