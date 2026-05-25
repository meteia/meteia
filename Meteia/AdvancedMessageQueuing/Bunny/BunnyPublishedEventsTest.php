<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use DateTimeImmutable;
use Meteia\EventSourcing\Fixtures\AggregateRecorded;
use Meteia\EventSourcing\StreamId;
use Meteia\EventSourcing\StreamVersion;
use Meteia\Events\PublishedEvent;
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
final class BunnyPublishedEventsTest extends TestCase
{
    public function testPublishUsesAChannelFromTheSharedClient(): void
    {
        /** @var list<array{0: string, 1: array<string, string>, 2: string}> $published */
        $published = [];
        $channel = $this->createStub(Channel::class);
        $channel->method('publish')->willReturnCallback(
            static function (mixed ...$arguments) use (&$published): bool {
                $published[] = [
                    self::stringArgument($arguments, 0),
                    self::stringArrayArgument($arguments, 1),
                    self::stringArgument($arguments, 2),
                ];

                return true;
            },
        );
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('channel')->willReturn($channel);
        $serializer = $this->createStub(SerializerInterface::class);
        $serializer->method('serialize')->willReturn('{}');

        $publishedEvent = PublishedEvent::fromMessage(
            StreamId::random(),
            StreamVersion::start(),
            new AggregateRecorded(),
            CausationId::random(),
            CorrelationId::random(),
            new DateTimeImmutable('2026-05-25T12:00:00+00:00'),
        );

        new BunnyPublishedEvents(
            new BunnyChannels($client, new NullLogger()),
            new NullLogger(),
            $serializer,
            self::scopeSource(),
        )->publish($publishedEvent);

        static::assertCount(1, $published);
        $message = $published[0] ?? static::fail('expected a published event message');
        static::assertSame('{}', $message[0]);
        static::assertSame(AggregateRecorded::class, str_replace('.', '\\', $message[2]));
        static::assertSame((string) $publishedEvent->messageId(), $message[1]['message-id'] ?? null);
        static::assertSame((string) $publishedEvent->streamId(), $message[1]['stream-id'] ?? null);
        static::assertSame((string) $publishedEvent->version(), $message[1]['stream-version'] ?? null);
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
