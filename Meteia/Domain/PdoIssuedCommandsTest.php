<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Commands\Command;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\ValueObjects\AggregateRootId;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PdoIssuedCommandsTest extends TestCase
{
    public function testAppendPersistsRowWithIdsIntact(): void
    {
        $db = $this->bootstrappedDatabase();
        $issued = new PdoIssuedCommands($db, $this->serializer());

        $aggregateRootId = SampleAggregateRootId::random();
        $causation = CausationId::random();
        $correlation = CorrelationId::random();
        $issuedAt = new DateTimeImmutable('2026-05-07 12:00:00');

        $issued->append(
            new CommandMetadata($aggregateRootId, $causation, $correlation, $issuedAt),
            new SampleCommand(),
        );

        $row = $db->fetchObject('SELECT * FROM issued_commands LIMIT 1');
        static::assertNotFalse($row);
        static::assertSame($causation->bytes(), $row->causation_id);
        static::assertSame($correlation->bytes(), $row->correlation_id);
        static::assertSame($aggregateRootId->bytes(), $row->aggregate_root_id);
        static::assertSame(SampleCommand::class, (string) $row->command_type);
    }

    public function testPendingReturnsEmptyForNowAsRehydrationIsDeferred(): void
    {
        $db = $this->bootstrappedDatabase();
        $issued = new PdoIssuedCommands($db, $this->serializer());

        $issued->append(
            new CommandMetadata(
                SampleAggregateRootId::random(),
                CausationId::random(),
                CorrelationId::random(),
                new DateTimeImmutable(),
            ),
            new SampleCommand(),
        );

        static::assertCount(0, $issued->pending());
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
                \assert($decoded !== false, 'Serialized command payload must be base64 encoded.');

                return unserialize($decoded, ['allowed_classes' => true]);
            }
        };
    }

    private function bootstrappedDatabase(): ExtendedPdoInterface
    {
        $db = new ExtendedPdo('sqlite::memory:');
        $db->exec('
            CREATE TABLE issued_commands (
                command_id        BLOB NOT NULL PRIMARY KEY,
                aggregate_root_id BLOB NOT NULL,
                command_type      BLOB NOT NULL,
                command           TEXT NOT NULL,
                causation_id      BLOB NOT NULL,
                correlation_id    BLOB NOT NULL,
                issued_at         TEXT NOT NULL,
                defer_until       TEXT NOT NULL
            );
        ');

        return $db;
    }
}

/**
 * @internal
 */
final readonly class SampleAggregateRootId extends AggregateRootId
{
    #[Override]
    public static function prefix(): string
    {
        return 'sar';
    }
}

/**
 * @internal
 *
 * @implements Command<void>
 */
final readonly class SampleCommand implements Command {}
