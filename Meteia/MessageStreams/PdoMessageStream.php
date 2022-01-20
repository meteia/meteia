<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\EventSourcing\Contracts\EventSourced;
use Meteia\MessageStreams\Contracts\Message;
use Meteia\MessageStreams\Contracts\MessageStream;
use Meteia\MessageStreams\Exceptions\FailedToAppendMessage;
use Meteia\Performance\Timings;
use Meteia\ValueObjects\Identity\UniqueId;
use ReflectionClass;

class PdoMessageStream implements MessageStream
{
    private array $snapshotVersions = [];

    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
        private Timings $timings,
    ) {
    }

    public function append(UniqueId $messageStreamId, MessageStreamSequence $messageStreamSequence, MessageTypeId $messageTypeId, Message $message): void
    {
        $query = '
            INSERT INTO message_streams (message_stream_id, message_stream_sequence, message_type_id, message)
            VALUE (UNHEX(:messageStreamId), :messageStreamSequence, UUID_TO_BIN(:messageTypeId), :message);
        ';
        $bindings = [
            'messageStreamId' => $messageStreamId->hex(),
            'messageStreamSequence' => (string) $messageStreamSequence,
            'messageTypeId' => (string) $messageTypeId,
            'message' => $this->messageSerializer->serialize($message),
        ];
        $success = $this->db->perform($query, $bindings);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }

    public function replay(UniqueId $messageStreamId, EventSourced $target): EventSourced
    {
        return $this->loadLatestSnapshot($messageStreamId, $target);
    }

    private function loadLatestSnapshot(UniqueId $messageStreamId, EventSourced $target): EventSourced
    {
        // TODO: Use APCu or similar cache? (benchmark first, PHP OpCache might be enough)
        if (!isset($this->snapshotVersions[$target::class])) {
            $rc = new ReflectionClass($target);
            $hash = substr(hash_file('sha256', $rc->getFileName()), 0, 32);
            $this->snapshotVersions[$target::class] = $hash;
        }

        dump($this->snapshotVersions[$target::class]);
        $query = '
            SELECT snapshot, message_stream_sequence
            FROM message_stream_snapshots
            WHERE message_stream_id = UNHEX(:messageStreamId) AND snapshot_version = UNHEX(:snapshotVersion)
            ORDER BY message_stream_sequence DESC
            LIMIT 1
        ';
        $bindings = [
            'messageStreamId' => $messageStreamId->hex(),
            'snapshotVersion' => $this->snapshotVersions[$target::class],
        ];
        $snapshotRow = $this->db->fetchObject($query, $bindings);
        if ($snapshotRow) {
            $target = $this->messageSerializer->unserialize($snapshotRow->snapshot);

            $query = '
                SELECT *
                FROM message_streams
                WHERE message_stream_id = UNHEX(:messageStreamId)
                AND message_stream_sequence > :messageStreamSequence
                ORDER BY message_stream_sequence;
            ';
            $bindings = [
                'messageStreamId' => $messageStreamId->hex(),
                'messageStreamSequence' => (string) $snapshotRow->message_stream_sequence,
            ];
        } else {
            $query = '
                SELECT *
                FROM message_streams
                WHERE message_stream_id = UNHEX(:messageStreamId)
                ORDER BY message_stream_sequence;
            ';
            $bindings = [
                'messageStreamId' => $messageStreamId->hex(),
            ];
        }

        $messageRows = $this->db->fetchObjects($query, $bindings);

        $replayStart = microtime(true);
        foreach ($messageRows as $messageRow) {
            /** @var Message $message */
            $message = $this->messageSerializer->unserialize($messageRow->message);
            $message->applyTo($target);
        }
        $replayDelta = (microtime(true) - $replayStart) * 1000;
        $this->timings->add($target::class . '.replay', $replayDelta);
        $this->timings->add($target::class . '.replayCount', count($messageRows));

        if ($replayDelta > 15 && count($messageRows) > 25) {
            $lastMessageRow = end($messageRows);
            $this->createSnapshot($messageStreamId, new MessageStreamSequence($lastMessageRow->message_stream_sequence), $target);
        }

        return $target;
    }

    private function createSnapshot(UniqueId $messageStreamId, MessageStreamSequence $messageStreamSequence, $target)
    {
        $this->timings->add($target::class . '.snapshotUpdate', 1);
        $query = '
            INSERT INTO message_stream_snapshots (message_stream_id, message_stream_sequence, snapshot_version, snapshot)
            VALUES (UNHEX(:messageStreamId), :messageStreamSequence, UNHEX(:snapshotVersion), :snapshot)
            ON DUPLICATE KEY UPDATE message_stream_sequence = VALUES(message_stream_sequence),
                                 snapshot_version = VALUES(snapshot_version),
                                 snapshot = VALUES(snapshot)
        ';
        $bindings = [
            'messageStreamId' => $messageStreamId->hex(),
            'messageStreamSequence' => (string) $messageStreamSequence,
            'snapshotVersion' => $this->snapshotVersions[$target::class],
            'snapshot' => $this->messageSerializer->serialize($target),
        ];
        $this->db->perform($query, $bindings);
    }
}
