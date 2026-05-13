<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final readonly class BindingAccepted implements BindingResult
{
    public function __construct(
        public VHostName $vhost,
        public QueueName $queue,
        public ExchangeName $exchange,
        public RoutingKey $routingKey,
    ) {}

    #[Override]
    public function accepted(): bool
    {
        return true;
    }

    #[Override]
    public function describe(): string
    {
        return "bound queue {$this->queue} to exchange {$this->exchange} on vhost {$this->vhost} with routing key {$this->routingKey}";
    }
}
