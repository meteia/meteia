<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Commands\Fixtures\ExampleCommand;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
final class PdoDelayedCommandOutboxTest extends TestCase
{
    public function testPublishAtStoresCommandAndMessageScope(): void
    {
        $db = self::database();
        $serializer = new MessageSerializer();
        $scope = self::scope();

        new PdoDelayedCommandOutbox(
            $db,
            $serializer,
            self::scopeSource($scope),
        )->publishAt(new ExampleCommand(), new DateTimeImmutable('2026-05-16 10:05:00.123456'));

        $row = $db->fetchObject('SELECT * FROM delayed_commands LIMIT 1');
        static::assertInstanceOf(stdClass::class, $row);
        static::assertSame(ExampleCommand::class, (string) $row->command_type);
        static::assertSame($scope->causationId()->bytes(), $row->causation_id);
        static::assertSame($scope->correlationId()->bytes(), $row->correlation_id);
        static::assertSame($scope->processId()->bytes(), $row->process_id);
        static::assertSame('2026-05-16 10:05:00.123456', $row->defer_until);
        static::assertInstanceOf(ExampleCommand::class, $serializer->unserialize((string) $row->command));
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
        $db->exec(self::schema());

        return $db;
    }

    private static function schema(): string
    {
        return '
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
        ';
    }
}
