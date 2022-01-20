<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\RabbitMQ\Contracts\MessageHandler;
use Meteia\RabbitMQ\Contracts\Queue;
use Psr\Log\LoggerInterface;
use Throwable;

class BunnyQueue implements Queue
{
    public function __construct(
        private LoggerInterface $log,
        private Channel $rmq,
    ) {
    }

    public function consume(string $queueName, MessageHandler $messageHandler): void
    {
        $this->rmq->consume(function (Message $message, Channel $channel, Client $bunny) use ($queueName, $messageHandler) {
            try {
                $messageHandler->handleMessageFromQueue($message->content, $queueName);
            } catch (Throwable $t) {
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), [
                    'queueName' => $queueName,
                ]);
            }
            $channel->ack($message);
        }, $queueName);
    }

    public function listen(): void
    {
        $this->rmq->qos(0, 5);
        $this->rmq->getClient()->run();
    }
}
