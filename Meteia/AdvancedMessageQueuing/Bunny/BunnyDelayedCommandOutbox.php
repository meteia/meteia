<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use DateTimeImmutable;
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
 * Publishes a command to a RabbitMQ `x-delayed-message` exchange with an
 * `x-delay` header set to the milliseconds remaining until `$when`. The
 * broker withholds the message and then routes it to the same per-command
 * queue that the standard commands worker is already consuming, so no
 * per-context worker is required.
 *
 * The exchange and queue binding are declared idempotently on every publish.
 * Requires the broker's `rabbitmq_delayed_message_exchange` plugin.
 */
final readonly class BunnyDelayedCommandOutbox implements DelayedCommandOutbox
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private DelayedCommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private MessageScopeSource $scopeSource,
        private Clock $clock,
    ) {}

    #[Override]
    public function publishAt(Command $command, DateTimeImmutable $when): void
    {
        $exchange = (string) $this->exchangeName;
        $this->channel->exchangeDeclare($exchange, exchangeType: 'x-delayed-message', durable: true, arguments: [
            'x-delayed-type' => 'direct',
        ]);
        $queueName = str_replace('\\', '.', $command::class);
        $this->channel->queueDeclare($queueName, durable: true);
        $this->channel->queueBind($queueName, $exchange, $queueName);

        $delayMs = $this->delayMs($when);
        $payload = $this->serializer->serialize($command, 'json');
        $context = MessageContext::fromScope($this->scopeSource->current());
        $headers = $context->headersWithMessageId((string) CommandId::random());
        $headers['x-delay'] = $delayMs;

        $this->channel->publish($payload, $headers, $exchange, $queueName);

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
}
