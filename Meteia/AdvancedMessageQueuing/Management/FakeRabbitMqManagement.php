<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final class FakeRabbitMqManagement implements RabbitMqManagement
{
    /** @var list<BindingAccepted> */
    public array $recorded = [];

    /** @var list<UnbindingAccepted> */
    public array $unbound = [];

    #[Override]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult {
        $result = new BindingAccepted($vhost, $queue, $exchange, $routingKey);
        $this->recorded[] = $result;

        return $result;
    }

    #[Override]
    public function unbindQueueFromExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): UnbindingResult {
        $result = new UnbindingAccepted($vhost, $queue, $exchange, $routingKey);
        $this->unbound[] = $result;

        return $result;
    }
}
