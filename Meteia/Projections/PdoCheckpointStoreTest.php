<?php

declare(strict_types=1);

namespace Meteia\Projections;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PdoCheckpointStoreTest extends TestCase
{
    public function testUnseenProjectionLoadsAtPositionZero(): void
    {
        $store = new PdoCheckpointStore($this->bootstrappedDatabase());

        $checkpoint = $store->load(new ProjectionName('users.read-model'));

        static::assertTrue($checkpoint->position()->equalTo(GlobalSequence::start()));
    }

    public function testSavedCheckpointRoundTrips(): void
    {
        $store = new PdoCheckpointStore($this->bootstrappedDatabase());
        $name = new ProjectionName('users.read-model');

        $store->save($name, new PersistedCheckpoint(new GlobalSequence(42)));

        $loaded = $store->load($name);
        static::assertSame(42, $loaded->position()->asInt());
    }

    public function testSavedCheckpointAdvancesOnReplace(): void
    {
        $store = new PdoCheckpointStore($this->bootstrappedDatabase());
        $name = new ProjectionName('users.read-model');

        $store->save($name, new PersistedCheckpoint(new GlobalSequence(10)));
        $store->save($name, new PersistedCheckpoint(new GlobalSequence(99)));

        static::assertSame(99, $store->load($name)->position()->asInt());
    }

    public function testCheckpointAdvancedToRejectsBackwardsMovement(): void
    {
        $checkpoint = new PersistedCheckpoint(new GlobalSequence(50));

        $this->expectException(InvalidArgumentException::class);
        (void) $checkpoint->advancedTo(new GlobalSequence(49));
    }

    private function bootstrappedDatabase(): ExtendedPdoInterface
    {
        $db = new ExtendedPdo('sqlite::memory:');
        $db->exec('
            CREATE TABLE projection_checkpoints (
                projection_name BLOB NOT NULL PRIMARY KEY,
                global_sequence INTEGER NOT NULL,
                updated_at      TEXT NOT NULL
            );
        ');

        return $db;
    }
}
