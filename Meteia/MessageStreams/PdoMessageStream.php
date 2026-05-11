<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\MessageStreams\Contracts\Message;
use Meteia\MessageStreams\Contracts\MessageStream;
use Meteia\MessageStreams\Exceptions\FailedToAppendMessage;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\UniqueId;
use Override;
use stdClass;

final readonly class PdoMessageStream implements MessageStream
{
    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
    ) {}

    #[Override]
    public function append(
        UniqueId $messageStreamId,
        MessageStreamSequence $sequence,
        MessageTypeId $messageTypeId,
        Message $message,
        MessageScope $scope,
    ): void {
        $success = $this->db->fetchAffected('
            INSERT INTO message_streams (
                message_stream_id, message_stream_sequence, message_type_id, message,
                causation_id, correlation_id, occurred_at
            )
            VALUES (
                :messageStreamId, :sequence, :messageTypeId, :message,
                :causationId, :correlationId, :occurredAt
            )
        ', [
            'messageStreamId' => $messageStreamId->bytes(),
            'sequence' => $sequence->asInt(),
            'messageTypeId' => $messageTypeId->bytes(),
            'message' => $this->messageSerializer->serialize($message),
            'causationId' => $scope->causationId()->bytes(),
            'correlationId' => $scope->correlationId()->bytes(),
            'occurredAt' => new DateTimeImmutable()->format('Y-m-d H:i:s.u'),
        ]);
        if (!$success) {
            throw new FailedToAppendMessage('SQL Issue : ' . $this->db->getPdo()->errorInfo()[2]);
        }
    }

    #[Override]
    public function read(UniqueId $messageStreamId): RecordedMessages
    {
        $rows = $this->db->fetchObjects('
            SELECT message_stream_id, message_stream_sequence, message,
                   causation_id, correlation_id, occurred_at
            FROM message_streams
            WHERE message_stream_id = :messageStreamId
            ORDER BY message_stream_sequence ASC
        ', ['messageStreamId' => $messageStreamId->bytes()]);

        return new RecordedMessages(array_map(fn(stdClass $row): RecordedMessage => $this->hydrate(
            $messageStreamId,
            $row,
        ), $rows));
    }

    private function hydrate(UniqueId $messageStreamId, stdClass $row): RecordedMessage
    {
        $messageRaw = $row->message;
        $causationIdRaw = $row->causation_id;
        $correlationIdRaw = $row->correlation_id;
        \assert(\is_string($messageRaw) && \is_string($causationIdRaw) && \is_string($correlationIdRaw));
        /** @var Message $message */
        $message = $this->messageSerializer->unserialize($messageRaw);
        $pending = new PendingMessage(
            $messageStreamId,
            new MessageStreamSequence((int) $row->message_stream_sequence),
            $message,
        );

        return new RecordedMessage(
            $pending,
            new CausationId($causationIdRaw),
            new CorrelationId($correlationIdRaw),
            new DateTimeImmutable((string) $row->occurred_at),
        );
    }
}
