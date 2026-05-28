<?php

declare(strict_types=1);

namespace Meteia\Realtime;

final class LiveViewSubscriptions
{
    /** @var array<string, LiveViewTopic> */
    private array $topics = [];

    public function subscribe(LiveViewTopic $topic): void
    {
        $this->topics[$topic->toNative()] = $topic;
    }

    /** @return list<LiveViewTopic> */
    public function topics(): array
    {
        return array_values($this->topics);
    }
}
