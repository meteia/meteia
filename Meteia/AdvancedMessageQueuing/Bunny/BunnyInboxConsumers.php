<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;

final class BunnyInboxConsumers
{
    /**
     * @var list<array{string, callable, callable}>
     */
    private array $consumers = [];

    public function add(string $queueName, callable $topology, callable $consumer): void
    {
        $this->consumers[] = [$queueName, $topology, $consumer];
    }

    public function subscribe(Channel $channel): void
    {
        foreach ($this->consumers as [$queueName, $topology, $consumer]) {
            $topology($channel);
            $channel->consume($consumer, $queueName);
        }
    }
}
