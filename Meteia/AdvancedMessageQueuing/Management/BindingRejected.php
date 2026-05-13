<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final readonly class BindingRejected implements BindingResult
{
    public function __construct(
        public VHostName $vhost,
        public QueueName $queue,
        public ExchangeName $exchange,
        public RoutingKey $routingKey,
        public string $reason,
    ) {}

    #[Override]
    public function accepted(): bool
    {
        return false;
    }

    #[Override]
    public function describe(): string
    {
        return "rejected binding queue {$this->queue} to exchange {$this->exchange} on vhost {$this->vhost} with routing key {$this->routingKey}: {$this->reason}";
    }
}
