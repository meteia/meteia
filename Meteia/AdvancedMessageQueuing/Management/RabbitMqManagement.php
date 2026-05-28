<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use NoDiscard;

interface RabbitMqManagement
{
    #[NoDiscard]
    public function bindQueueToExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): BindingResult;

    #[NoDiscard]
    public function unbindQueueFromExchange(
        VHostName $vhost,
        QueueName $queue,
        ExchangeName $exchange,
        RoutingKey $routingKey,
    ): UnbindingResult;
}
