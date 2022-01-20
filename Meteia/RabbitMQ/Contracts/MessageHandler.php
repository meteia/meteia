<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\Contracts;

interface MessageHandler
{
    public function handleMessageFromQueue(string $body, string $queueName): void;
}
