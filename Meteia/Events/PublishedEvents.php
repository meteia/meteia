<?php

declare(strict_types=1);

namespace Meteia\Events;

interface PublishedEvents
{
    public function publish(PublishedEvent $event): void;
}
