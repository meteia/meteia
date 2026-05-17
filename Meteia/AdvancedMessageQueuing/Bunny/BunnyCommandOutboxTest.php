<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\CommandDelivery;
use Meteia\Commands\CommandId;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
final class BunnyCommandOutboxTest extends TestCase
{
    public function testPublishDeliveryUsesStoredCommandIdentityAndScope(): void
    {
        /** @var list<array{0: string, 1: array<string, string>, 2: string, 3: string}> $published */
        $published = [];
        $channel = $this->createStub(Channel::class);
        $channel->method('publish')->willReturnCallback(
            static function (mixed ...$arguments) use (&$published): bool {
                $published[] = [
                    self::stringArgument($arguments, 0),
                    self::stringArrayArgument($arguments, 1),
                    self::stringArgument($arguments, 2),
                    self::stringArgument($arguments, 3),
                ];

                return true;
            },
        );
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('serialize')->willReturn('{}');
        $commandId = CommandId::random();
        $scope = new MessageScope(CorrelationId::random(), CausationId::random(), ProcessId::random());

        new BunnyCommandOutbox(
            $channel,
            new NullLogger(),
            new CommandsExchangeName('Meteia.Commands'),
            $serializer,
            self::scopeSource(),
        )->publishDelivery(new CommandDelivery($commandId, new ExampleCommand(), $scope));

        static::assertCount(1, $published);
        $message = $this->publishedMessage($published);
        static::assertSame('{}', $message[0]);
        static::assertSame('Meteia.Commands', $message[2]);
        static::assertSame('Meteia.Commands.Fixtures.ExampleCommand', $message[3]);
        static::assertSame((string) $commandId, $message[1]['message-id'] ?? null);
        static::assertSame((string) $scope->causationId(), $message[1]['causation-id'] ?? null);
        static::assertSame((string) $scope->correlationId(), $message[1]['correlation-id'] ?? null);
        static::assertSame((string) $scope->processId(), $message[1]['process-id'] ?? null);
    }

    private static function scopeSource(): MessageScopeSource
    {
        return new readonly class implements MessageScopeSource {
            #[Override]
            public function current(): MessageScope
            {
                return new MessageScope(CorrelationId::random(), CausationId::random(), ProcessId::random());
            }
        };
    }

    /**
     * @param list<array{0: string, 1: array<string, string>, 2: string, 3: string}> $published
     * @return array{0: string, 1: array<string, string>, 2: string, 3: string}
     */
    private function publishedMessage(array $published): array
    {
        static::assertArrayHasKey(0, $published);

        return $published[0] ?? static::fail('expected a published message');
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    private static function stringArgument(array $arguments, int $index): string
    {
        static::assertArrayHasKey($index, $arguments);
        // @mago-expect analysis:mixed-assignment -- Captured PHPUnit invocation arguments are narrowed immediately below.
        $argument = $arguments[$index] ?? static::fail('expected argument');
        static::assertIsString($argument);

        return $argument;
    }

    /**
     * @param array<array-key, mixed> $arguments
     * @return array<string, string>
     */
    private static function stringArrayArgument(array $arguments, int $index): array
    {
        static::assertArrayHasKey($index, $arguments);
        // @mago-expect analysis:mixed-assignment -- Captured PHPUnit invocation arguments are narrowed immediately below.
        $argument = $arguments[$index] ?? static::fail('expected argument');
        static::assertIsArray($argument);
        // @mago-expect analysis:mixed-assignment -- Captured header values are narrowed inside the loop.
        foreach ($argument as $key => $value) {
            static::assertIsString($key);
            static::assertIsString($value);
        }

        /** @var array<string, string> $argument */
        return $argument;
    }
}
