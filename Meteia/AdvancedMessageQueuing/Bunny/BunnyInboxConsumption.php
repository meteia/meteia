<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

final class BunnyInboxConsumption
{
    private int $messageLimit;

    private int $processedMessages;

    public function __construct()
    {
        $this->untilShutdown();
    }

    public function untilShutdown(): void
    {
        $this->messageLimit = 0;
        $this->processedMessages = 0;
    }

    public function oneMessage(): void
    {
        $this->messageLimit = 1;
        $this->processedMessages = 0;
    }

    public function recordHandledMessage(): void
    {
        $this->processedMessages++;
    }

    public function isSatisfied(): bool
    {
        return $this->messageLimit > 0 && $this->processedMessages >= $this->messageLimit;
    }
}
