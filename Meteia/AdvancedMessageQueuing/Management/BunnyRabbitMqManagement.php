<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Bunny\Client;
use Bunny\ChannelInterface;
use Override;
use Throwable;

final readonly class BunnyRabbitMqManagement implements RabbitMqManagement
{
    public function __construct(
        private Client $client,
    ) {}

    #[Override]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult {
        $channel = null;
        try {
            $channel = $this->client->channel();
            $channel->exchangeDeclare(
                exchange: $exchange->toNative(),
                exchangeType: 'topic',
                durable: true,
            );
            $channel->queueBind(
                exchange: $exchange->toNative(),
                queue: $queue->toNative(),
                routingKey: $routingKey->toNative(),
            );
        } catch (Throwable $e) {
            return new BindingRejected($vhost, $queue, $exchange, $routingKey, $e->getMessage());
        } finally {
            $this->closeChannel($channel);
        }

        return new BindingAccepted($vhost, $queue, $exchange, $routingKey);
    }

    #[Override]
    public function unbindQueueFromExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): UnbindingResult {
        $channel = null;
        try {
            $channel = $this->client->channel();
            $channel->queueUnbind(
                queue: $queue->toNative(),
                exchange: $exchange->toNative(),
                routingKey: $routingKey->toNative(),
            );
        } catch (Throwable $e) {
            return new UnbindingRejected($vhost, $queue, $exchange, $routingKey, $e->getMessage());
        } finally {
            $this->closeChannel($channel);
        }

        return new UnbindingAccepted($vhost, $queue, $exchange, $routingKey);
    }

    private function closeChannel(?ChannelInterface $channel): void
    {
        try {
            $channel?->close();
        } catch (Throwable) {
            return;
        }
    }
}
