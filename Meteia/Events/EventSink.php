<?php

declare(strict_types=1);

namespace Meteia\Events;

use Meteia\ValueObjects\Identity\MessageScope;

interface EventSink
{
    public function drain(PublishedEvent $event, MessageScope $scope): void;
}
