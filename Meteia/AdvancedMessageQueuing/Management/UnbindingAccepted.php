<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Management;

use Override;

final readonly class UnbindingAccepted implements UnbindingResult
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
        return "unbound queue {$this->queue} from exchange {$this->exchange} on vhost {$this->vhost} with routing key {$this->routingKey}";
    }
}
