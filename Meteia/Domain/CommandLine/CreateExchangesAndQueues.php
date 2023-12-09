<?php

declare(strict_types=1);

namespace Meteia\Domain\CommandLine;

use Bunny\Channel;
use Meteia\CommandLine\Command;
use Meteia\Domain\Configuration\DomainCommandsExchangeName;
use Meteia\Domain\DomainCommandQueueNames;
use Meteia\Domain\DomainEventQueueMappings;
use Meteia\Domain\DomainEventToExchangeAndQueues;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;

readonly class CreateExchangesAndQueues implements Command
{
    public function __construct(
        private LoggerInterface $log,
        private DomainCommandsExchangeName $exchangeName,
        private DomainCommandQueueNames $domainCommandQueueNames,
        private DomainEventQueueMappings $domainEventQueueMappings,
        private Channel $channel,
    ) {
    }

    #[Override]
    public function execute(): void
    {
        $ok = $this->channel->exchangeDeclare(exchange: $this->exchangeName, exchangeType: 'direct', passive: false, durable: true, autoDelete: false);
        $this->log->info('Command Exchange Declared', ['exchange' => $this->exchangeName, 'status' => $ok ? 'ok' : 'failed']);

        foreach ($this->domainCommandQueueNames as $queueName => $commandClassname) {
            $ok = $this->channel->queueDeclare(queue: $queueName, passive: false, durable: true, exclusive: false, autoDelete: false);
            $this->log->info('Command Queue Declared', ['queue' => $queueName, 'status' => $ok ? 'ok' : 'failed']);

            $ok = $this->channel->queueBind(queue: $queueName, exchange: $this->exchangeName, routingKey: $queueName);
            $this->log->info('Command Queue Bound', ['queue' => $queueName, 'exchange' => $this->exchangeName, 'status' => $ok ? 'ok' : 'failed']);
        }

        /** @var DomainEventToExchangeAndQueues $domainEventQueueMapping */
        foreach ($this->domainEventQueueMappings as $domainEventQueueMapping) {
            $exchangeName = $domainEventQueueMapping->exchange;
            $ok = $this->channel->exchangeDeclare(exchange: $exchangeName, exchangeType: 'fanout', passive: false, durable: true, autoDelete: false);
            $this->log->info('Event Exchange Declared', ['exchange' => $exchangeName, 'status' => $ok ? 'ok' : 'failed']);

            foreach ($domainEventQueueMapping->queues as $queueName => $eventHandlerClassName) {
                $ok = $this->channel->queueDeclare(queue: $queueName, passive: false, durable: true, exclusive: false, autoDelete: false);
                $this->log->info('Event Queue Declared', ['queue' => $queueName, 'status' => $ok ? 'ok' : 'failed']);

                $ok = $this->channel->queueBind(queue: $queueName, exchange: $exchangeName, routingKey: $queueName);
                $this->log->info('Event Queue Bound', ['queue' => $queueName, 'exchange' => $exchangeName, 'status' => $ok ? 'ok' : 'failed']);
            }
        }
    }

    #[Override]
    public static function description(): string
    {
        return 'Create exchanges and queues';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }
}
