<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\PhpAmqpLib;

use Meteia\RabbitMQ\Contracts\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class PhpAmqpLibConsumer
{
    public function __construct(
        private AMQPChannel $channel,
        private MessageHandler $messageHandler,
        private string $queueName,
    ) {
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, [$this, 'handleAMQPMessage']);
    }

    public function handleAMQPMessage(AMQPMessage $message): void
    {
        try {
            $this->messageHandler->handleMessageFromQueue($message->getBody(), $this->queueName);
            $message->ack();
        } catch (Throwable $e) {
            $message->reject();
            throw $e;
        }
    }
}
