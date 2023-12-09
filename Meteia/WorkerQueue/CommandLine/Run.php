<?php

declare(strict_types=1);

namespace Meteia\WorkerQueue\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\DependencyInjection\Container;
use Meteia\RabbitMQ\Contracts\MessageHandler;
use Meteia\RabbitMQ\Contracts\Queue;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;

readonly class Run implements Command, MessageHandler
{
    public function __construct(
        private LoggerInterface $log,
        private Queue $queue,
        private Container $container,
    ) {
    }

    #[Override]
    public function execute(): void
    {
        $this->queue->consume('worker_queue', $this);
        $this->log->debug('Listening on queue');
        $this->queue->listen();
    }

    #[Override]
    public static function description(): string
    {
        return 'Run the worker queue';
    }

    #[Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    #[Override]
    public function handleMessageFromQueue(string $body, string $queueName): void
    {
        $this->log->debug('Received message from queue', [
            'queueName' => $queueName,
            'body' => $body,
        ]);
    }
}
