<?php

declare(strict_types=1);

namespace Meteia\MessageStreams;

use DateTimeImmutable;
use Meteia\MessageStreams\Contracts\Message;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\UniqueId;

final readonly class RecordedMessage
{
    public function __construct(
        private PendingMessage $pending,
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function messageStreamId(): UniqueId
    {
        return $this->pending->messageStreamId();
    }

    public function sequence(): MessageStreamSequence
    {
        return $this->pending->sequence();
    }

    public function message(): Message
    {
        return $this->pending->message();
    }

    public function causedBy(): CausationId
    {
        return $this->causationId;
    }

    public function correlatedTo(): CorrelationId
    {
        return $this->correlationId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
