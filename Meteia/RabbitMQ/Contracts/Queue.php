<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\Contracts;

interface Queue
{
    public function consume(string $queueName, MessageHandler $messageHandler): void;

    public function listen(): void;
}
