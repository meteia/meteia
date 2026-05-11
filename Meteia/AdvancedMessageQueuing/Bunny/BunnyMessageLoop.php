<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Bunny\Protocol\MethodQueueDeclareOkFrame;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;

final readonly class BunnyMessageLoop
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
    ) {}

    public function channel(): Channel
    {
        return $this->channel;
    }

    public function runUntilShutdown(string $shutdownExchangeName): void
    {
        $this->channel->exchangeDeclare($shutdownExchangeName, exchangeType: 'fanout', durable: true);
        $this->log->info('Declared Exchange', ['exchange' => $shutdownExchangeName]);

        /** @var MethodQueueDeclareOkFrame $result */
        $result = $this->channel->queueDeclare(exclusive: true);
        $this->log->info('Declared Queue', ['queue' => $result->queue]);

        $this->channel->queueBind(exchange: $shutdownExchangeName, queue: $result->queue);
        $this->channel->consume(function (Message $message, Channel $channel, Client $bunny): void {
            $this->log->info('Shutdown Message Received');
            $channel->ack($message);
            $bunny->disconnect();
            sleep(random_int(1, 5));

            exit(0);
        }, $result->queue);

        Loop::run();
    }
}
