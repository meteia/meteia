<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\UnitOfWorkContext;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

return;
function db(): ExtendedPdoInterface
{
    return new ExtendedPdo('sqlite::memory:');
}

function init(ExtendedPdoInterface $pdo): PdoEventStream
{
    $query = <<<'SQL'
        CREATE TABLE events (
            aggregate_root_id  BINARY(20)                         NOT NULL,
            aggregate_sequence BIGINT UNSIGNED                    NOT NULL,
            event_type_id      BINARY(16)                         NOT NULL,
            event              MEDIUMTEXT                         NOT NULL,
            created            DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            correlation_id     BINARY(20)                         NOT NULL,
            causation_id       BINARY(20)                         NOT NULL,
            CONSTRAINT aggregate_sequence UNIQUE (aggregate_root_id, aggregate_sequence)
        );
        CREATE INDEX event_type_id ON events(event_type_id);

        CREATE TABLE event_snapshots (
            aggregate_root_id  BINARY(20)                         NOT NULL,
            aggregate_sequence BIGINT UNSIGNED                    NOT NULL,
            aggregate_hash     BINARY(16)                         NOT NULL,
            snapshot           MEDIUMTEXT                         NOT NULL,
            created            DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            CONSTRAINT aggregate_root_id UNIQUE (aggregate_root_id)
        );
    SQL;
    $pdo->exec($query);

    return new PdoEventStream($pdo, new MessageSerializer(), new Timings());
}

// class FakeUnitOfWorkContext implements UnitOfWorkContext
// {
//    public CommandMessages $commandMessages;
//
//    public EventMessages $eventMessages;
//
//
//    public function commitCommandMessages(CommandMessages $commandMessages)
//    {
//        $this->commandMessages = $commandMessages;
//    }
//
//
//    public function commitEventMessages(EventMessages $eventMessages)
//    {
//        $this->eventMessages = $eventMessages;
//    }
// }

it('appends and', static function (): void {
    /** @var \TestCase $this */

    // Arrange
    $db = db();
    $messageStream = init($db);
    $agid = TestAggregateRootId::random();
    $event = new TestDomainEvent();
    $tar = new CountingAggregateRoot([$event]);

    // Act
    $messageStream->append($agid, 0, EventTypeId::random(), $event, CausationId::random(), CorrelationId::random());
    $messageStream->replay($agid, $tar);

    // Assert
    $tar->assertReplayed();
});

it('supports snapshots', static function (): void {
    /** @var \TestCase $this */

    // Arrange
    $db = db();
    $messageStream = init($db);
    $agid = TestAggregateRootId::random();
    $event = new TestDomainEvent();

    $i = 0;

    // Act
    $expectedEvents = [];
    for (; $i < random_int(30, 50); ++$i) {
        $expectedEvents[] = $event;
        $messageStream->append(
            $agid,
            $i,
            EventTypeId::random(),
            $event,
            CausationId::random(),
            CorrelationId::random(),
        );
    }

    // First replay to trigger snapshot creation
    $tar = new CountingAggregateRoot($expectedEvents);
    $tar = $messageStream->replay($agid, $tar);
    for (; $i < random_int(50, 80); ++$i) {
        $expectedEvents[] = $event;
        $messageStream->append(
            $agid,
            $i,
            EventTypeId::random(),
            $event,
            CausationId::random(),
            CorrelationId::random(),
        );
    }

    // Second replay
    $tar = new CountingAggregateRoot($expectedEvents);
    $tar = $messageStream->replay($agid, $tar);

    // Assert
    $tar->assertSnapshot();
});

/**
 * @codeCoverageIgnore
 */
class CountingAggregateRoot implements EventSourced
{
    private $sequence = -1;

    private $actualEvents = [];

    private bool $wakeupCalled = false;

    public function __construct(
        private array $expectedEvents,
    ) {}

    public function __wakeup(): void
    {
        $this->wakeupCalled = true;
    }

    #[\Override]
    public function commitInto(UnitOfWorkContext $unitOfWorkContext): void
    {
    }

    #[\Override]
    public function handleEventMessage(AggregateRootId $aggregateRootId, DomainEvent $event, int $eventSequence): void
    {
        // FIXME: Better way to force snapshots
        usleep(1000);
        assertEquals($this->sequence + 1, $eventSequence);
        $this->sequence = $eventSequence;
        $this->actualEvents[] = $event;
    }

    public function assertReplayed(): void
    {
        assertEquals($this->expectedEvents, $this->actualEvents);
    }

    public function assertSnapshot(): void
    {
        assertTrue($this->wakeupCalled);
    }
}

/**
 * @codeCoverageIgnore
 */
class TestAggregateRootId extends AggregateRootId
{
    #[\Override]
    public static function prefix(): string
    {
        return 'tar';
    }
}

/**
 * @codeCoverageIgnore
 */
class TestDomainEvent implements DomainEvent
{
    public function __construct() {}

    #[\Override]
    public static function eventTypeId(): EventTypeId
    {
        return EventTypeId::random();
    }
}
