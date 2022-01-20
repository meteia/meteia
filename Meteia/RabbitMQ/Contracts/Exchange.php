<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\Contracts;

interface Exchange
{
    public function publish(string $body, string $exchange, string $routingKey = '', $properties = []): void;
}
