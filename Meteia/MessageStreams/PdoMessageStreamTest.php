<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Meteia\MessageStreams\Contracts\Message;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Meteia\ValueObjects\Identity\UniqueId;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PdoMessageStreamTest extends TestCase
{
    public function testAppendAndReadRoundTripsCorrelationAndCausation(): void
    {
        $stream = new PdoMessageStream($this->bootstrappedDatabase(), $this->serializer());
        $streamId = StubStreamId::random();
        $causation = CausationId::random();
        $correlation = CorrelationId::random();
        $scope = new MessageScope($correlation, $causation, ProcessId::random());

        $stream->append($streamId, new MessageStreamSequence(0), MessageTypeId::random(), new SimpleMessage(), $scope);

        $messages = $stream->read($streamId);
        static::assertCount(1, $messages);
        $first = $messages[0];
        \assert($first instanceof RecordedMessage);
        static::assertSame((string) $causation, (string) $first->causedBy());
        static::assertSame((string) $correlation, (string) $first->correlatedTo());
    }

    private function serializer(): MessageSerializer
    {
        return new class extends MessageSerializer {
            public function __construct() {}

            #[Override]
            public function serialize(mixed $value): string
            {
                return base64_encode(serialize($value));
            }

            #[Override]
            public function unserialize(string $value): mixed
            {
                $decoded = base64_decode($value, true);
                \assert($decoded !== false);

                return unserialize($decoded, ['allowed_classes' => true]);
            }
        };
    }

    private function bootstrappedDatabase(): ExtendedPdoInterface
    {
        $db = new ExtendedPdo('sqlite::memory:');
        $db->exec('
            CREATE TABLE message_streams (
                message_stream_id       BLOB NOT NULL,
                message_stream_sequence INTEGER NOT NULL,
                message_type_id         BLOB NOT NULL,
                message                 TEXT NOT NULL,
                causation_id            BLOB NOT NULL,
                correlation_id          BLOB NOT NULL,
                occurred_at             TEXT NOT NULL,
                UNIQUE (message_stream_id, message_stream_sequence)
            );
        ');

        return $db;
    }
}

/**
 * @internal
 */
final readonly class StubStreamId extends UniqueId
{
    #[Override]
    public static function prefix(): string
    {
        return 'msg';
    }
}

/**
 * @internal
 */
final readonly class SimpleMessage implements Message {}
