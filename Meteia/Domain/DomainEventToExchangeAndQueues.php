<?php

declare(strict_types=1);

namespace Meteia\Domain;

class DomainEventToExchangeAndQueues
{
    public function __construct(
        public string $className,
        public string $exchange,
        public array $queues,
    ) {
    }
}
