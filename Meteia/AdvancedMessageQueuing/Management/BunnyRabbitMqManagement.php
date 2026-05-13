<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Bunny\ChannelInterface;
use Override;
use Throwable;

final readonly class BunnyRabbitMqManagement implements RabbitMqManagement
{
    public function __construct(
        private ChannelInterface $channel,
    ) {}

    #[Override]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult {
        try {
            $this->channel->exchangeDeclare(
                exchange: $exchange->toNative(),
                exchangeType: 'topic',
                durable: true,
            );
            $this->channel->queueBind(
                exchange: $exchange->toNative(),
                queue: $queue->toNative(),
                routingKey: $routingKey->toNative(),
            );
        } catch (Throwable $e) {
            return new BindingRejected($vhost, $queue, $exchange, $routingKey, $e->getMessage());
        }

        return new BindingAccepted($vhost, $queue, $exchange, $routingKey);
    }
}
