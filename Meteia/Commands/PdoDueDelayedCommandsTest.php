<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Commands\Fixtures\CapturingCommandDeliveries;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\Commands\Fixtures\FailingCommandDeliveries;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Time\FrozenClock;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use stdClass;

/**
 * @internal
 */
final class PdoDueDelayedCommandsTest extends TestCase
{
    public function testDispatchPublishesOnlyDueUnpublishedCommands(): void
    {
        $db = self::database();
        $serializer = new MessageSerializer();
        $scope = self::scope();
        $published = new CapturingCommandDeliveries();

        $due = $this->schedule($db, $serializer, $scope, '2026-05-16 09:59:59.000000');
        $future = $this->schedule($db, $serializer, $scope, '2026-05-16 10:00:01.000000');
        $alreadyPublished = $this->schedule($db, $serializer, $scope, '2026-05-16 09:59:58.000000');
        $db->fetchAffected('UPDATE delayed_commands SET published_at = :publishedAt WHERE command_id = :commandId', [
            'publishedAt' => '2026-05-16 09:59:59.500000',
            'commandId' => $alreadyPublished->bytes(),
        ]);

        $count = $this->commands($db, $serializer, $published)->dispatch(10);

        static::assertSame(1, $count);
        static::assertCount(1, $published->deliveries);
        static::assertTrue($due->equalTo($this->firstDelivery($published)->commandId()));
        static::assertNotNull($this->row($db, $due)->published_at);
        static::assertNull($this->row($db, $future)->published_at);
        static::assertSame('2026-05-16 09:59:59.500000', $this->row($db, $alreadyPublished)->published_at);
    }

    public function testDispatchClaimsStaleClaimedCommand(): void
    {
        $db = self::database();
        $serializer = new MessageSerializer();
        $scope = self::scope();
        $published = new CapturingCommandDeliveries();
        $commandId = $this->schedule($db, $serializer, $scope, '2026-05-16 09:59:59.000000');
        $db->fetchAffected('
            UPDATE delayed_commands
            SET claimed_at = :claimedAt, claim_id = :claimId
            WHERE command_id = :commandId
        ', [
            'claimedAt' => '2026-05-16 09:50:00.000000',
            'claimId' => 'previous-claim',
            'commandId' => $commandId->bytes(),
        ]);

        $count = $this->commands($db, $serializer, $published)->dispatch(10);

        static::assertSame(1, $count);
        static::assertTrue($commandId->equalTo($this->firstDelivery($published)->commandId()));
    }

    public function testDispatchRecordsFailureWithoutPublishingRow(): void
    {
        $db = self::database();
        $serializer = new MessageSerializer();
        $scope = self::scope();
        $commandId = $this->schedule($db, $serializer, $scope, '2026-05-16 09:59:59.000000');

        $count = $this->commands($db, $serializer, new FailingCommandDeliveries())->dispatch(10);

        $row = $this->row($db, $commandId);
        static::assertSame(0, $count);
        static::assertNull($row->published_at);
        static::assertSame('delivery failed', $row->failure);
        static::assertNotNull($row->failed_at);
    }

    private function commands(
        ExtendedPdoInterface $db,
        MessageSerializer $serializer,
        CommandDeliveries $deliveries,
    ): PdoDueDelayedCommands {
        return new PdoDueDelayedCommands(
            $db,
            $serializer,
            $deliveries,
            new FrozenClock(new DateTimeImmutable('2026-05-16 10:00:00.000000')),
            new NullLogger(),
        );
    }

    private function schedule(
        ExtendedPdoInterface $db,
        MessageSerializer $serializer,
        MessageScope $scope,
        string $when,
    ): CommandId {
        new PdoDelayedCommandOutbox($db, $serializer, self::scopeSource($scope))
            ->publishAt(new ExampleCommand(), new DateTimeImmutable($when));

        $row = $db->fetchObject('SELECT command_id FROM delayed_commands ORDER BY rowid DESC LIMIT 1');
        static::assertInstanceOf(stdClass::class, $row);

        return new CommandId((string) $row->command_id);
    }

    private function row(ExtendedPdoInterface $db, CommandId $commandId): stdClass
    {
        $row = $db->fetchObject('SELECT * FROM delayed_commands WHERE command_id = :commandId', [
            'commandId' => $commandId->bytes(),
        ]);
        static::assertInstanceOf(stdClass::class, $row);

        return $row;
    }

    private function firstDelivery(CapturingCommandDeliveries $published): CommandDelivery
    {
        static::assertArrayHasKey(0, $published->deliveries);

        return $published->deliveries[0] ?? static::fail('expected a published command delivery');
    }

    private static function scope(): MessageScope
    {
        return new MessageScope(
            CorrelationId::random(),
            CausationId::random(),
            ProcessId::random(),
        );
    }

    private static function scopeSource(MessageScope $scope): MessageScopeSource
    {
        return new readonly class($scope) implements MessageScopeSource {
            public function __construct(
                private MessageScope $scope,
            ) {}

            #[Override]
            public function current(): MessageScope
            {
                return $this->scope;
            }
        };
    }

    private static function database(): ExtendedPdoInterface
    {
        $db = new ExtendedPdo('sqlite::memory:');
        $db->exec('
            CREATE TABLE delayed_commands (
                command_id     BLOB NOT NULL PRIMARY KEY,
                command_type   BLOB NOT NULL,
                command        TEXT NOT NULL,
                causation_id   BLOB NOT NULL,
                correlation_id BLOB NOT NULL,
                process_id     BLOB NOT NULL,
                defer_until    TEXT NOT NULL,
                claimed_at     TEXT NULL,
                claim_id       TEXT NULL,
                published_at   TEXT NULL,
                failed_at      TEXT NULL,
                failure        TEXT NULL
            );
        ');

        return $db;
    }
}
