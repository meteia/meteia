<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Bunny\Client;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyConnectionOptions;
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class IsolatedBunnyRabbitMqManagement implements RabbitMqManagement
{
    public function __construct(
        private BunnyConnectionOptions $connectionOptions,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult {
        $client = null;
        try {
            $client = $this->connectionOptions->client();
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
            $this->close($client);
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
        $client = null;
        try {
            $client = $this->connectionOptions->client();
            $channel = $client->channel();
            $channel->queueUnbind(
                queue: $queue->toNative(),
                exchange: $exchange->toNative(),
                routingKey: $routingKey->toNative(),
            );
        } catch (Throwable $e) {
            return new UnbindingRejected($vhost, $queue, $exchange, $routingKey, $e->getMessage());
        } finally {
            $this->close($client);
        }

        return new UnbindingAccepted($vhost, $queue, $exchange, $routingKey);
    }

    private function close(?Client $client): void
    {
        try {
            if ($client?->canDisconnect() === true) {
                $client->disconnect();
            }
        } catch (Throwable $throwable) {
            $this->log->debug('Ignored isolated RabbitMQ client disconnect failure', [
                'reason' => $throwable->getMessage(),
            ]);
        }
    }
}
