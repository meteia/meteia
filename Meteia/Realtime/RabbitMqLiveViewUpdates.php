<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Bunny\Channel;
use Override;
use Psr\Log\LoggerInterface;

final readonly class RabbitMqLiveViewUpdates implements LiveViewUpdates
{
    public function __construct(
        private Channel $channel,
        private LiveViewExchange $exchange,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function publish(LiveViewTopic $topic, string $html): void
    {
        $exchange = $this->exchange->toNative();

        $this->channel->exchangeDeclare($exchange, exchangeType: 'topic', durable: true);
        $this->channel->publish(
            $html,
            ['content-type' => 'text/html'],
            $exchange,
            $topic->toNative(),
        );

        $this->log->info('Published live view update', [
            'exchange' => $exchange,
            'topic' => $topic->toNative(),
        ]);
    }
}
