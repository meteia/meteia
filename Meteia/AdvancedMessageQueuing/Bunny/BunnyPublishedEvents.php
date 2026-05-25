<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Meteia\AdvancedMessageQueuing\MessageContext;
use Meteia\Events\PublishedEvent;
use Meteia\Events\PublishedEvents;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyPublishedEvents implements PublishedEvents
{
    public function __construct(
        private BunnyChannels $channels,
        private LoggerInterface $log,
        private SerializerInterface $serializer,
        private MessageScopeSource $scopeSource,
    ) {}

    #[Override]
    public function publish(PublishedEvent $event): void
    {
        $domainEvent = $event->fact();
        $exchangeName = str_replace('\\', '.', $domainEvent::class);
        $channel = $this->channels->publishingChannel();
        $channel->exchangeDeclare($exchangeName, exchangeType: 'fanout', durable: true);

        $payload = $this->serializer->serialize($domainEvent, 'json');
        $context = MessageContext::fromScope($this->scopeSource->current());
        $headers = $context->headersWithMessageId((string) $event->messageId());
        $headers['stream-id'] = (string) $event->streamId();
        $headers['stream-version'] = (string) $event->version();
        $headers['occurred-at'] = $event->occurredAt()->format(DATE_ATOM);
        $channel->publish($payload, $headers, $exchangeName);
        $this->log->info('Published Event', [
            'event' => $domainEvent::class,
            'exchange' => $exchangeName,
            'streamId' => $event->streamId(),
            'streamVersion' => $event->version(),
        ]);
    }
}
