<?php

declare(strict_types=1);

namespace Meteia\EventSourcing;

use DI\Container;
use Meteia\AdvancedMessageQueuing\Contracts\Exchange;
use Meteia\AdvancedMessageQueuing\Contracts\MessageHandler;
use Meteia\AdvancedMessageQueuing\Contracts\Queue;
use Meteia\AdvancedMessageQueuing\DerivedNames;
use Meteia\Application\ApplicationNamespace;
use Meteia\Domain\Contracts\DomainEvent;
use Meteia\Domain\Contracts\DomainEventHandler;
use Meteia\MessageStreams\MessageSerializer;
use Psr\Log\LoggerInterface;

class EventBus implements MessageHandler
{
    use DerivedNames;

    public function __construct(
        private LoggerInterface $log,
        private ApplicationNamespace $applicationNamespace,
        private Exchange $exchange,
        private Queue $queue,
        private MessageSerializer $messageSerializer,
        private Container $container,
    ) {}

    public function publish(DomainEvent $event): void
    {
        $exchange = $this->exchangeNameFromEvent($event::class);
        $body = $this->messageSerializer->serialize($event);
        $this->exchange->publish($body, $exchange, '');
    }

    public function consumeFor(string $eventHandler): void
    {
        \assert(is_subclass_of($eventHandler, DomainEventHandler::class));
        $this->queue->consume($this->queueNameFromEventHandler($eventHandler), $this);
    }

    public function listen(): void
    {
        $this->queue->listen();
    }

    public function handleMessageFromQueue(string $body, string $queueName): void
    {
        try {
            $domainEvent = $this->messageSerializer->unserialize($body);
            \assert(is_subclass_of($domainEvent, DomainEvent::class));
            $domainEventHandlerClass = $this->eventHandlerFromQueueName($this->applicationNamespace, $queueName);

            /** @var DomainEventHandler $domainEventHandler */
            $domainEventHandler = $this->container->get($domainEventHandlerClass);
            \assert(is_subclass_of($domainEventHandler, DomainEventHandler::class));
            $this->container->call([$domainEventHandler, 'on'], [$domainEvent]);
        } catch (\Throwable $t) {
            $this->log->critical($t->getMessage(), [
                'queueName' => $queueName,
                'file' => $t->getFile(),
                'line' => $t->getLine(),
            ]);

            return;
        }

        $this->log->info('DomainEventHandled', [
            'queueName' => $queueName,
        ]);
    }
}
