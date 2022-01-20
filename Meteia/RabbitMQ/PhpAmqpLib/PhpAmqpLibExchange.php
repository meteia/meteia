<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\PhpAmqpLib;

use Meteia\RabbitMQ\Contracts\Exchange;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class PhpAmqpLibExchange implements Exchange
{
    public function __construct(
        private AMQPChannel $channel,
        private LoggerInterface $log,
    ) {
    }

    public function publish(string $body, string $exchange, string $routingKey = '', $properties = []): void
    {
        $msg = new AMQPMessage($body, $properties);
        $this->channel->basic_publish($msg, $exchange, $routingKey);
        $this->log->debug('Published Message', ['exchange' => $exchange]);
    }
}
