<?php

declare(strict_types=1);

use Meteia\AdvancedMessageQueuing\Bunny\BunnyEventInbox;
use Meteia\AdvancedMessageQueuing\Bunny\BunnyPublishedEvents;
use Meteia\Events\Events;
use Meteia\Events\EventInbox;
use Meteia\Events\EventSinks;
use Meteia\Events\OutboxPublisher;
use Meteia\Events\PsrEvents;
use Meteia\Events\PsrEventSinks;
use Meteia\Events\PublishedEvents;

return [
    Events::class => PsrEvents::class,
    EventSinks::class => PsrEventSinks::class,
    PublishedEvents::class => BunnyPublishedEvents::class,
    EventInbox::class => BunnyEventInbox::class,
    OutboxPublisher::class => static fn(
        \Aura\Sql\ExtendedPdoInterface $db,
        \Meteia\MessageStreams\MessageSerializer $serializer,
        PublishedEvents $publishedEvents,
        \Psr\Log\LoggerInterface $log,
    ): OutboxPublisher => new OutboxPublisher($db, $serializer, $publishedEvents, $log),
];
