<?php

declare(strict_types=1);

namespace Meteia\MessageStreams\Contracts;

use Meteia\MessageStreams\MessageStreamSequence;
use Meteia\MessageStreams\MessageTypeId;
use Meteia\MessageStreams\RecordedMessages;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\UniqueId;

interface MessageStream
{
    public function append(
        UniqueId $messageStreamId,
        MessageStreamSequence $sequence,
        MessageTypeId $messageTypeId,
        Message $message,
        MessageScope $scope,
    ): void;

    public function read(UniqueId $messageStreamId): RecordedMessages;
}
