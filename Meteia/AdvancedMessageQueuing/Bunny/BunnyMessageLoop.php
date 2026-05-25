<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\ClientInterface;
use Bunny\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use Throwable;

final class BunnyMessageLoop
{
    private ?Client $client = null;

    public function __construct(
        private readonly BunnyConnectionOptions $connectionOptions,
        private LoggerInterface $log,
    ) {}

    public function channel(): Channel
    {
        $channel = $this->client()->channel();
        \assert($channel instanceof Channel, 'Bunny client must return a concrete Bunny channel');

        return $channel;
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
        if ($this->client === null) {
            return;
        }

        try {
            if ($this->client->canDisconnect()) {
                $this->client->disconnect(connectionStatus: ClientInterface::RAW_CONNECTION_INACTIVE);
            }
        } catch (Throwable $throwable) {
            $this->log->warning('Failed to close AMQP connection during worker recovery', [
                'exception' => $throwable,
            ]);
        } finally {
            $this->client = null;
        }
    }

    private function client(): Client
    {
        if ($this->client === null) {
            $this->client = $this->connectionOptions->client();
        }

        return $this->client;
    }
}
