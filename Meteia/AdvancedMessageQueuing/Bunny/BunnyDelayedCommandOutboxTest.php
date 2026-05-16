<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use DateTimeImmutable;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\Configuration\DelayedCommandsExchangeName;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\Time\FrozenClock;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class BunnyDelayedCommandOutboxTest extends TestCase
{
    public function testPublishesDelayedCommandThroughTtlDeadLetterQueue(): void
    {
        $exchanges = [];
        $queues = [];
        $bindings = [];
        $published = [];
        $channel = $this->capturingChannel($exchanges, $queues, $bindings, $published);

        $this->outbox(
            $channel,
            new FrozenClock(new DateTimeImmutable('2026-05-16 10:00:00.000000')),
        )->publishAt(
            new ExampleCommand(),
            new DateTimeImmutable('2026-05-16 10:00:05.000000'),
        );

        static::assertSame([
            ['Meteia.Commands', 'direct', true, []],
            ['Meteia.DelayedCommands', 'direct', true, []],
        ], $exchanges);
        static::assertSame([
            ['Meteia.Commands.Fixtures.ExampleCommand', true, []],
            [
                'Meteia.Commands.Fixtures.ExampleCommand.Delayed.5000ms',
                true,
                [
                    'x-message-ttl' => 5000,
                    'x-dead-letter-exchange' => 'Meteia.Commands',
                    'x-dead-letter-routing-key' => 'Meteia.Commands.Fixtures.ExampleCommand',
                    'x-queue-type' => 'quorum',
                    'x-expires' => 86_405_000,
                ],
            ],
        ], $queues);
        static::assertSame([
            ['Meteia.Commands', 'Meteia.Commands.Fixtures.ExampleCommand', 'Meteia.Commands.Fixtures.ExampleCommand'],
            [
                'Meteia.DelayedCommands',
                'Meteia.Commands.Fixtures.ExampleCommand.Delayed.5000ms',
                'Meteia.Commands.Fixtures.ExampleCommand.Delayed.5000ms',
            ],
        ], $bindings);
        $message = $this->publishedMessage($published);
        static::assertSame('{}', $message['body']);
        static::assertSame('Meteia.DelayedCommands', $message['exchange']);
        static::assertSame('Meteia.Commands.Fixtures.ExampleCommand.Delayed.5000ms', $message['routingKey']);
        static::assertArrayNotHasKey('x-delay', $message['headers']);
        static::assertSame('application/json', $message['headers']['content-type'] ?? null);
        static::assertMatchesRegularExpression('/^cmd_/', (string) ($message['headers']['message-id'] ?? ''));
    }

    public function testPublishesPastCommandDirectlyToCommandExchange(): void
    {
        $exchanges = [];
        $queues = [];
        $bindings = [];
        $published = [];
        $channel = $this->capturingChannel($exchanges, $queues, $bindings, $published);

        $this->outbox(
            $channel,
            new FrozenClock(new DateTimeImmutable('2026-05-16 10:00:00.000000')),
        )->publishAt(
            new ExampleCommand(),
            new DateTimeImmutable('2026-05-16 09:59:59.999999'),
        );

        static::assertSame([
            ['Meteia.Commands', 'direct', true, []],
        ], $exchanges);
        static::assertSame([
            ['Meteia.Commands.Fixtures.ExampleCommand', true, []],
        ], $queues);
        static::assertSame([
            ['Meteia.Commands', 'Meteia.Commands.Fixtures.ExampleCommand', 'Meteia.Commands.Fixtures.ExampleCommand'],
        ], $bindings);
        $message = $this->publishedMessage($published);
        static::assertSame('Meteia.Commands', $message['exchange']);
        static::assertSame('Meteia.Commands.Fixtures.ExampleCommand', $message['routingKey']);
        static::assertArrayNotHasKey('x-delay', $message['headers']);
    }

    /**
     * @param list<array{0: string, 1: string, 2: bool, 3: array<string, mixed>}> $exchanges
     * @param list<array{0: string, 1: bool, 2: array<string, mixed>}> $queues
     * @param list<array{0: string, 1: string, 2: string}> $bindings
     * @param list<array{body: string, headers: array<string, mixed>, exchange: string, routingKey: string}> $published
     */
    private function capturingChannel(
        array &$exchanges,
        array &$queues,
        array &$bindings,
        array &$published,
    ): Channel {
        $channel = $this->createStub(Channel::class);
        $channel->method('exchangeDeclare')->willReturnCallback(
            static function (mixed ...$arguments) use (&$exchanges): bool {
                $exchange = self::stringArgument($arguments, 0, '');
                $exchangeType = self::stringArgument($arguments, 1, 'direct');
                $durable = self::falseOrBoolArgument($arguments, 3);
                $exchangeArguments = self::arrayArgument($arguments, 7);
                $exchanges[] = [$exchange, $exchangeType, $durable, $exchangeArguments];

                return true;
            },
        );
        $channel->method('queueDeclare')->willReturnCallback(
            static function (mixed ...$arguments) use (&$queues): bool {
                $queue = self::stringArgument($arguments, 0, '');
                $durable = self::falseOrBoolArgument($arguments, 2);
                $queueArguments = self::arrayArgument($arguments, 6);
                $queues[] = [$queue, $durable, $queueArguments];

                return true;
            },
        );
        $channel->method('queueBind')->willReturnCallback(
            static function (mixed ...$arguments) use (&$bindings): bool {
                $exchange = self::stringArgument($arguments, 0, '');
                $queue = self::stringArgument($arguments, 1, '');
                $routingKey = self::stringArgument($arguments, 2, '');
                $bindings[] = [$exchange, $queue, $routingKey];

                return true;
            },
        );
        $channel->method('publish')->willReturnCallback(
            static function (mixed ...$arguments) use (&$published): bool {
                $published[] = [
                    'body' => self::stringArgument($arguments, 0, ''),
                    'headers' => self::arrayArgument($arguments, 1),
                    'exchange' => self::stringArgument($arguments, 2, ''),
                    'routingKey' => self::stringArgument($arguments, 3, ''),
                ];

                return true;
            },
        );

        return $channel;
    }

    /**
     * @param list<array{body: string, headers: array<string, mixed>, exchange: string, routingKey: string}> $published
     * @return array{body: string, headers: array<string, mixed>, exchange: string, routingKey: string}
     */
    private function publishedMessage(array $published): array
    {
        if (!\array_key_exists(0, $published)) {
            static::fail('expected one published message');
        }

        return $published[0];
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    private static function stringArgument(array $arguments, int $index, string $default): string
    {
        if (!\array_key_exists($index, $arguments)) {
            return $default;
        }

        // @mago-expect analysis:possibly-undefined-int-array-index -- PHPUnit passes invocation arguments positionally.
        // @mago-expect analysis:mixed-assignment -- The helper narrows the captured invocation argument below.
        $argument = $arguments[$index];
        if (!\is_string($argument)) {
            static::fail('captured Bunny argument must be a string');
        }

        return $argument;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    private static function falseOrBoolArgument(array $arguments, int $index): bool
    {
        if (!\array_key_exists($index, $arguments)) {
            return false;
        }

        // @mago-expect analysis:possibly-undefined-int-array-index -- PHPUnit passes invocation arguments positionally.
        // @mago-expect analysis:mixed-assignment -- The helper narrows the captured invocation argument below.
        $argument = $arguments[$index];
        if (!\is_bool($argument)) {
            static::fail('captured Bunny argument must be a boolean');
        }

        return $argument;
    }

    /**
     * @param array<array-key, mixed> $arguments
     * @return array<string, mixed>
     */
    private static function arrayArgument(array $arguments, int $index): array
    {
        if (!\array_key_exists($index, $arguments)) {
            return [];
        }

        // @mago-expect analysis:possibly-undefined-int-array-index -- PHPUnit passes invocation arguments positionally.
        // @mago-expect analysis:mixed-assignment -- The helper narrows the captured invocation argument below.
        $argument = $arguments[$index];
        if (!\is_array($argument)) {
            static::fail('captured Bunny argument must be an array');
        }

        foreach (\array_keys($argument) as $key) {
            if (\is_string($key)) {
                continue;
            }

            static::fail('captured Bunny argument array keys must be strings');
        }

        /** @var array<string, mixed> $argument */
        return $argument;
    }

    private function outbox(Channel $channel, FrozenClock $clock): BunnyDelayedCommandOutbox
    {
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('serialize')->willReturn('{}');

        $scope = new MessageScope(
            CorrelationId::random(),
            CausationId::random(),
            ProcessId::random(),
        );
        $scopeSource = $this->createStub(MessageScopeSource::class);
        $scopeSource->method('current')->willReturn($scope);

        return new BunnyDelayedCommandOutbox(
            $channel,
            $this->createStub(LoggerInterface::class),
            new CommandsExchangeName('Meteia.Commands'),
            new DelayedCommandsExchangeName('Meteia.DelayedCommands'),
            $serializer,
            $scopeSource,
            $clock,
        );
    }
}
