<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;

final class BunnyMessageLoop
{
    public function __construct(
        private readonly BunnyChannels $channels,
        private LoggerInterface $log,
    ) {}

    public function channel(): Channel
    {
        return $this->channels->newChannel();
    }

    public function runUntilShutdown(Channel $channel, string $shutdownExchangeName): void
    {
        $channel->exchangeDeclare($shutdownExchangeName, exchangeType: 'fanout', durable: true);
        $this->log->info('Declared Exchange', ['exchange' => $shutdownExchangeName]);

        $result = $channel->queueDeclare(exclusive: true);
        $this->log->info('Declared Queue', ['queue' => $result->queue]);

        $channel->queueBind(exchange: $shutdownExchangeName, queue: $result->queue);
        $channel->consume(function (Message $message, Channel $channel, Client $bunny): void {
            $this->log->info('Shutdown Message Received');
            $channel->ack($message);
            $bunny->disconnect();
            sleep(random_int(1, 5));

            exit(0);
        }, $result->queue);

        Loop::run();
    }

    public function reset(): void
    {
        $this->channels->reset();
    }
}
