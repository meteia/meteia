<?php

declare(strict_types=1);

namespace Meteia\RabbitMQ;

use Meteia\Application\ApplicationNamespace;

trait DerivedNames
{
    protected function exchangeNameFromEvent(string $eventName): string
    {
        $exchangeNameParts = explode('\\', $eventName);
        assert(count($exchangeNameParts) === 4, 'Domain events are routed by strict convention');
        unset($exchangeNameParts[0], $exchangeNameParts[2]);

        return implode('.', $exchangeNameParts);
    }

    protected function exchangeNameFromEventHandler(string $eventHandler): string
    {
        $exchangeNameParts = explode('\\', $eventHandler);
        assert(count($exchangeNameParts) === 6, 'Domain events are routed by strict convention');
        $exchangeNameParts = array_slice($exchangeNameParts, 3);
        array_pop($exchangeNameParts);

        return implode('.', $exchangeNameParts);
    }

    protected function queueNameFromEventHandler(string $eventHandler): string
    {
        $exchangeNameParts = explode('\\', $eventHandler);
        assert(count($exchangeNameParts) === 6, 'Domain events are routed by strict convention');

        unset($exchangeNameParts[0], $exchangeNameParts[2]);

        return implode('.', $exchangeNameParts);
    }

    protected function eventHandlerFromQueueName(ApplicationNamespace $namespace, string $queueName): string
    {
        $exchangeNameParts = explode('.', $queueName);
        assert(count($exchangeNameParts) === 4, 'Domain events are routed by strict convention');

        array_unshift($exchangeNameParts, $namespace);
        array_splice($exchangeNameParts, 2, 0, 'DomainEventHandlers');

        return implode('\\', $exchangeNameParts);
    }
}
