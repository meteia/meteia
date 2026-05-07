<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use Meteia\MessageStreams\Contracts\Message;
use Meteia\ValueObjects\Identity\UniqueId;

final readonly class PendingMessage
{
    public function __construct(
        private UniqueId $messageStreamId,
        private MessageStreamSequence $sequence,
        private Message $message,
    ) {}

    public function messageStreamId(): UniqueId
    {
        return $this->messageStreamId;
    }

    public function sequence(): MessageStreamSequence
    {
        return $this->sequence;
    }

    public function message(): Message
    {
        return $this->message;
    }
}
