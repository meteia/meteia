<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\Command;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandSink;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use function React\Async\async;

final readonly class BunnyCommandInbox implements CommandInbox
{
    private BunnyInboxConsumption $consumption;

    private BunnyInboxConsumers $consumers;

    public function __construct(
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private BunnyMessageLoop $loop,
        private AmbientMessageScopeSource $scopeSource,
    ) {
        $this->consumption = new BunnyInboxConsumption();
        $this->consumers = new BunnyInboxConsumers();
    }

    #[Override]
    public function subscribe(string $commandClassName, CommandSink $sink): void
    {
        $queueName = str_replace('\\', '.', $commandClassName);
        $this->log->info('Subscribing Command Sink', ['queue' => $queueName]);

        $this->consumers->add(
            $queueName,
            function (Channel $channel) use ($queueName): void {
                $channel->exchangeDeclare((string) $this->exchangeName, durable: true);
                $channel->queueDeclare(queue: $queueName, durable: true);
                $channel->queueBind(exchange: (string) $this->exchangeName, queue: $queueName, routingKey: $queueName);
            },
            async(function (Message $message, Channel $channel, Client $bunny) use (
                $commandClassName,
                $queueName,
                $sink,
            ): void {
                try {
                    $messageScope = new BunnyCommandMessageScope($message);
                    $commandId = $messageScope->commandId();
                    $scope = $messageScope->scope();
                    $command = $this->serializer->deserialize($message->content, $commandClassName, 'json');
                    \assert($command instanceof Command, 'deserialized command must implement Command');
                    $this->scopeSource->using($scope, function () use (
                        $sink,
                        $command,
                        $scope,
                        $queueName,
                        $commandId,
                    ): void {
                        $this->log->info('Received Command', [
                            'queueName' => $queueName,
                            'commandId' => $commandId,
                        ]);
                        $sink->drain($command, $scope);
                    });
                    $channel->ack($message);

                    $this->consumption->recordHandledMessage();
                    if ($this->consumption->isSatisfied()) {
                        $this->log->info('Once mode: disconnecting after processing one message', [
                            'queueName' => $queueName,
                        ]);
                        $bunny->disconnect();
                        exit(0);
                    }
                } catch (Throwable $t) {
                    $channel->nack($message, requeue: true);
                    $this->log->error($t->getMessage(), ['queueName' => $queueName]);
                }
            }),
        );
    }

    #[Override]
    public function run(): void
    {
        $this->consumption->untilShutdown();
        $this->runUntilShutdown('CommandWorkers.Shutdown');
    }

    #[Override]
    public function runOnce(): void
    {
        $this->consumption->oneMessage();
        $this->runUntilShutdown('CommandWorkers.Shutdown');
    }

    private function runUntilShutdown(string $shutdownExchangeName): void
    {
        while (true) {
            try {
                $channel = $this->loop->channel();
                $this->consumers->subscribe($channel);
                $this->loop->runUntilShutdown($channel, $shutdownExchangeName);
                $this->log->warning('Command worker message loop stopped without a shutdown message; restarting');
            } catch (Throwable $throwable) {
                $this->log->error('Command worker AMQP failure; restarting', [
                    'exception' => $throwable,
                ]);
            }

            $this->loop->reset();
            sleep(1);
        }
    }
}
