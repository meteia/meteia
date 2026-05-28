<?php

declare(strict_types=1);

namespace Meteia\Realtime;

interface LiveViewUpdates
{
    public function publish(LiveViewTopic $topic, string $html): void;
}
