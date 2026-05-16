<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Bunny\Client;
use Bunny\ChannelInterface;
use Override;
use Throwable;

final readonly class BunnyRabbitMqManagement implements RabbitMqManagement
{
    /**
     * @param array{
     *     host: string,
     *     port: int,
     *     vhost: string,
     *     user: string,
     *     password: string,
     *     timeout: int,
     *     heartbeat: float,
     *     keepAlive: bool
     * } $connectionOptions
     */
    public function __construct(
        private array $connectionOptions,
    ) {}

    #[Override]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult {
        $client = null;
        $channel = null;
        try {
            $client = new Client($this->connectionOptions);
            $client->connect();
            $channel = $client->channel();
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
            $this->disconnectClient($client);
        }

        return new BindingAccepted($vhost, $queue, $exchange, $routingKey);
    }

    private function closeChannel(?ChannelInterface $channel): void
    {
        try {
            $channel?->close();
        } catch (Throwable) {
            return;
        }
    }

    private function disconnectClient(?Client $client): void
    {
        try {
            $client?->disconnect();
        } catch (Throwable) {
            return;
        }
    }
}
