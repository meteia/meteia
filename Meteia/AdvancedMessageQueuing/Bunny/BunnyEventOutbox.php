<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Meteia\AdvancedMessageQueuing\MessageContext;
use Meteia\Events\Event;
use Meteia\Events\EventId;
use Meteia\Events\EventOutbox;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyEventOutbox implements EventOutbox
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private SerializerInterface $serializer,
        private MessageContext $context,
    ) {}

    #[\Override]
    public function publish(Event $event): void
    {
        $exchangeName = str_replace('\\', '.', $event::class);
        $payload = $this->serializer->serialize($event, 'json');
        $this->channel->publish(
            $payload,
            $this->context->headersWithMessageId((string) EventId::random()),
            $exchangeName,
        );
        $this->log->info('Published Event', [
            'event' => $event::class,
            'exchange' => $exchangeName,
        ]);
    }
}
