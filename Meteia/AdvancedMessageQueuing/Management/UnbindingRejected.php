<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final readonly class UnbindingRejected implements UnbindingResult
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
        return "rejected unbinding queue {$this->queue} from exchange {$this->exchange} on vhost {$this->vhost} with routing key {$this->routingKey}: {$this->reason}";
    }
}
