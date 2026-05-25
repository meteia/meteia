<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Message;
use Meteia\Commands\CommandId;
use Meteia\Commands\ReplyDestination;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Throwable;

final readonly class BunnyCommandMessageScope
{
    private CommandId $commandId;

    private MessageScope $scope;

    public function __construct(
        private Message $message,
    ) {
        $this->commandId = $this->commandIdFromHeaders();
        $this->scope = new MessageScope(
            $this->correlationIdFromHeaders(),
            CausationId::fromHex($this->commandId->hex()),
            $this->processIdFromHeaders(),
        );
    }

    public function commandId(): CommandId
    {
        return $this->commandId;
    }

    public function scope(): MessageScope
    {
        return $this->scope;
    }

    public function replyDestination(): ?ReplyDestination
    {
        foreach ($this->headers('reply-to') as $candidate) {
            if ($candidate !== '') {
                return new ReplyDestination($candidate);
            }
        }

        return null;
    }

    private function commandIdFromHeaders(): CommandId
    {
        foreach ($this->headers('x-meteia-command-id', 'message-id', 'amqp-message-id') as $candidate) {
            if (!str_starts_with($candidate, 'cmd_')) {
                continue;
            }

            try {
                return CommandId::fromToken($candidate);
            } catch (Throwable) {
                continue;
            }
        }

        return CommandId::random();
    }

    private function correlationIdFromHeaders(): CorrelationId
    {
        foreach ($this->headers('x-meteia-correlation-id', 'correlation-id') as $candidate) {
            if (!str_starts_with($candidate, 'crr_')) {
                continue;
            }

            try {
                return CorrelationId::fromToken($candidate);
            } catch (Throwable) {
                continue;
            }
        }

        return CorrelationId::random();
    }

    private function processIdFromHeaders(): ProcessId
    {
        foreach ($this->headers('x-meteia-process-id', 'process-id') as $candidate) {
            if (!str_starts_with($candidate, 'pid_')) {
                continue;
            }

            try {
                return ProcessId::fromToken($candidate);
            } catch (Throwable) {
                continue;
            }
        }

        return ProcessId::random();
    }

    /**
     * @return list<string>
     */
    private function headers(string ...$names): array
    {
        $headers = [];
        foreach ($names as $name) {
            if (!\is_scalar($this->message->headers[$name] ?? null)) {
                continue;
            }

            $headers[] = (string) $this->message->headers[$name];
        }

        return $headers;
    }
}
