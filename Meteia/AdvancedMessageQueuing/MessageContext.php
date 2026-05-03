<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing;

use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;

final readonly class MessageContext
{
    public function __construct(
        private CausationId $causationId,
        private CorrelationId $correlationId,
        private ProcessId $processId,
    ) {}

    /**
     * @return array<string, string>
     */
    public function headersWithMessageId(string $messageId): array
    {
        return [
            'message-id' => $messageId,
            'content-type' => 'application/json',
            'correlation-id' => (string) $this->correlationId,
            'causation-id' => (string) $this->causationId,
            'process-id' => (string) $this->processId,
        ];
    }
}
