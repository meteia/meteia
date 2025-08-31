<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Meteia\Domain\ValueObjects\AggregateRootId;
use Meteia\MessageStreams\Contracts\Message;
use Meteia\MessageStreams\Contracts\MessageStream;

return;
function db(): ExtendedPdoInterface
{
    return new ExtendedPdo('sqlite::memory:');
}

function init(ExtendedPdoInterface $pdo): PdoEventStream
{
    $query = <<<'SQL'
    CREATE TABLE message_streams (
        message_stream_id       BINARY(20)                         NOT NULL,
        message_stream_sequence BIGINT UNSIGNED                    NOT NULL,
        message_type_id         BINARY(20)                         NOT NULL,
        message                 MEDIUMTEXT                         NOT NULL,
        created                 DATETIME DEFAULT CURRENT_TIMESTAMP NULL,
        CONSTRAINT message_stream_id UNIQUE (message_stream_id, message_stream_sequence)
    );
    CREATE INDEX message_type_id ON message_streams(message_type_id);
    SQL;
    $pdo->exec($query);

    return new PdoEventStream($pdo, new MessageSerializer());
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

it('stores a stream on commit', static function (): void {
    /** @var \TestCase $this */

    // Arrange
    $db = db();
    $messageStream = init($db);

    $agid = new AggregateRootId();

    $tm = new TestMessage($agid, 1);

    $tm->appendTo($messageStream);
});

/**
 * @codeCoverageIgnore
 */
class TestEntity
{
    private int $value = 0;

    public function increaseValue(): void
    {
        ++$this->value;
    }

    public function currentValue(): int
    {
        return $this->value;
    }
}

/**
 * @codeCoverageIgnore
 */
class TestMessage implements Message
{
    public function __construct(
        private AggregateRootId $someId,
        private int $sequence,
    ) {}

    public function appendTo(MessageStream $messageStream): void
    {
        $messageStream->append(
            new MessageStreamId($this->someId),
            new MessageStreamSequence($this->sequence),
            new MessageTypeId('30FA63C6-FB9B-430A-B4E3-4FE98E37E853'),
            $this,
        );
    }

    /**
     * @param TestEntity $target
     */
    public function applyTo($target): void
    {
        $target->increaseValue();
    }
}
