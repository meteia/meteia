<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use DateTimeImmutable;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\Configuration\DelayedCommandsExchangeName;
use Meteia\AdvancedMessageQueuing\MessageContext;
use Meteia\Commands\Command;
use Meteia\Commands\CommandId;
use Meteia\Commands\DelayedCommandOutbox;
use Meteia\Time\Clock;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Publishes a command through a TTL queue that dead-letters into the standard
 * command exchange when the requested delay has elapsed.
 */
final readonly class BunnyDelayedCommandOutbox implements DelayedCommandOutbox
{
    private const int DELAY_QUEUE_CLEANUP_MS = 86_400_000;

    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private CommandsExchangeName $commandsExchangeName,
        private DelayedCommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private MessageScopeSource $scopeSource,
        private Clock $clock,
    ) {}

    #[Override]
    public function publishAt(Command $command, DateTimeImmutable $when): void
    {
        $queueName = str_replace('\\', '.', $command::class);
        $delayMs = $this->delayMs($when);
        $payload = $this->serializer->serialize($command, 'json');
        $context = MessageContext::fromScope($this->scopeSource->current());
        $headers = $context->headersWithMessageId((string) CommandId::random());

        $this->declareCommandTarget($queueName);

        $exchange = (string) $this->commandsExchangeName;
        $routingKey = $queueName;
        if ($delayMs > 0) {
            $delayQueueName = $this->delayQueueName($queueName, $delayMs);
            $this->declareDelayQueue($queueName, $delayQueueName, $delayMs);
            $exchange = (string) $this->exchangeName;
            $routingKey = $delayQueueName;
        }

        $this->channel->publish($payload, $headers, $exchange, $routingKey);

        $this->log->info('Published Delayed Command', [
            'command' => $command::class,
            'exchange' => $exchange,
            'queue' => $queueName,
            'delayMs' => $delayMs,
            'when' => $when->format('Y-m-d H:i:s.u'),
        ]);
    }

    private function delayMs(DateTimeImmutable $when): int
    {
        $now = $this->clock->now();

        return max(
            0,
            (int) (
                ($when->getTimestamp() * 1000) + intdiv((int) $when->format('u'), 1000)
                - (($now->getTimestamp() * 1000) + intdiv((int) $now->format('u'), 1000))
            ),
        );
    }

    private function declareCommandTarget(string $queueName): void
    {
        $this->channel->exchangeDeclare((string) $this->commandsExchangeName, durable: true);
        $this->channel->queueDeclare($queueName, durable: true);
        $this->channel->queueBind(
            exchange: (string) $this->commandsExchangeName,
            queue: $queueName,
            routingKey: $queueName,
        );
    }

    private function declareDelayQueue(string $commandQueueName, string $delayQueueName, int $delayMs): void
    {
        $this->channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $this->channel->queueDeclare($delayQueueName, durable: true, arguments: [
            'x-message-ttl' => $delayMs,
            'x-dead-letter-exchange' => (string) $this->commandsExchangeName,
            'x-dead-letter-routing-key' => $commandQueueName,
            'x-queue-type' => 'quorum',
            'x-expires' => $delayMs + self::DELAY_QUEUE_CLEANUP_MS,
        ]);
        $this->channel->queueBind(
            exchange: (string) $this->exchangeName,
            queue: $delayQueueName,
            routingKey: $delayQueueName,
        );
    }

    private function delayQueueName(string $commandQueueName, int $delayMs): string
    {
        return $commandQueueName . '.Delayed.' . $delayMs . 'ms';
    }
}
