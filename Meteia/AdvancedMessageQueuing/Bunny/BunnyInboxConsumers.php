<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;

final class BunnyInboxConsumers
{
    /**
     * @var list<array{string, callable}>
     */
    private array $consumers = [];

    public function add(string $queueName, callable $consumer): void
    {
        $this->consumers[] = [$queueName, $consumer];
    }

    public function subscribe(Channel $channel): void
    {
        foreach ($this->consumers as [$queueName, $consumer]) {
            $channel->consume($consumer, $queueName);
        }
    }
}
