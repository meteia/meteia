<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\PhpAmqpLib;

use Meteia\RabbitMQ\Contracts\MessageHandler;
use Meteia\RabbitMQ\Contracts\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Log\LoggerInterface;

class PhpAmqpLibQueue implements Queue
{
    public function __construct(
        private LoggerInterface $log,
        private AMQPChannel $channel,
    ) {
    }

    public function consume(string $queueName, MessageHandler $messageHandler): void
    {
        new PhpAmqpLibConsumer($this->channel, $messageHandler, $queueName);
        $this->log->debug('Consuming Queue', ['queueName' => $queueName]);
    }

    public function listen(): void
    {
        $this->log->debug('Waiting on Channel Callbacks', ['count' => count($this->channel->callbacks)]);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->log->debug('Channel callbacks complete');
    }
}
