<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final class FakeRabbitMqManagement implements RabbitMqManagement
{
    /** @var list<BindingAccepted> */
    public array $recorded = [];

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
}
