<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Identity;

use NoDiscard;

final readonly class MessageScope
{
    public function __construct(
        private CorrelationId $correlationId,
        private CausationId $causationId,
        private ProcessId $processId,
    ) {}

    public function correlationId(): CorrelationId
    {
        return $this->correlationId;
    }

    public function causationId(): CausationId
    {
        return $this->causationId;
    }

    public function processId(): ProcessId
    {
        return $this->processId;
    }

    #[NoDiscard]
    public function causedBy(UniqueId $messageId): self
    {
        return clone($this, ['causationId' => CausationId::fromHex($messageId->hex())]);
    }

    #[NoDiscard]
    public function inheriting(CorrelationId $correlationId, UniqueId $messageId): self
    {
        return clone($this, [
            'correlationId' => $correlationId,
            'causationId' => CausationId::fromHex($messageId->hex()),
        ]);
    }
}
