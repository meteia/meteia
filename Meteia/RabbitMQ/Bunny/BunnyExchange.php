<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\Bunny;

use Bunny\Channel;
use Meteia\RabbitMQ\Contracts\Exchange;
use Psr\Log\LoggerInterface;

readonly class BunnyExchange implements Exchange
{
    public function __construct(
        private LoggerInterface $log,
        private Channel $rmq,
    ) {
    }

    public function publish(string $body, string $exchange, string $routingKey = '', $properties = []): void
    {
        $this->rmq->publish($body, $properties, $exchange, $routingKey);
        $this->log->debug('Published Message', ['exchange' => $exchange]);
    }
}
