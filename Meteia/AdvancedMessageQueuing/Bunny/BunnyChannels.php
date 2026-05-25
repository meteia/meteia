<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\ClientInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class BunnyChannels
{
    private ?Channel $publishingChannel = null;

    public function __construct(
        private readonly Client $client,
        private LoggerInterface $log,
    ) {}

    public function newChannel(): Channel
    {
        return $this->openChannel();
    }

    public function publishingChannel(): Channel
    {
        $this->publishingChannel ??= $this->openChannel();

        return $this->publishingChannel;
    }

    public function reset(): void
    {
        $this->publishingChannel = null;

        try {
            if ($this->client->canDisconnect()) {
                $this->client->disconnect(connectionStatus: ClientInterface::RAW_CONNECTION_INACTIVE);
            }
        } catch (Throwable $throwable) {
            $this->log->warning('Failed to close AMQP connection during worker recovery', [
                'exception' => $throwable,
            ]);
        }
    }

    private function openChannel(): Channel
    {
        $channel = $this->client->channel();
        \assert($channel instanceof Channel, 'Bunny client must return a concrete Bunny channel');

        return $channel;
    }
}
