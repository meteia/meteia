<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\Contracts\MessageHandler;
use Meteia\AdvancedMessageQueuing\Contracts\Queue;
use Psr\Log\LoggerInterface;

readonly class BunnyQueue implements Queue
{
    public function __construct(
        private LoggerInterface $log,
        private Channel $rmq,
    ) {
    }

    public function consume(string $queueName, MessageHandler $messageHandler): void
    {
        $this->rmq->consume(function (Message $message, Channel $channel, Client $bunny) use ($queueName, $messageHandler): void {
            try {
                $messageHandler->handleMessageFromQueue($message->content, $queueName);
            } catch (\Throwable $t) {
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), [
                    'queueName' => $queueName,
                ]);
            }
            $channel->ack($message);
        }, $queueName);
    }

    public function listen(int $prefetchCount = 1): void
    {
        $this->rmq->qos(0, $prefetchCount);
        $this->rmq->getClient()->run();
    }
}
