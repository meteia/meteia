<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Meteia\AdvancedMessageQueuing\AmbientMessageScopeSource;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\Commands\Command;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandInbox;
use Meteia\Commands\CommandSink;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use UnexpectedValueException;
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
        $channel = $this->loop->channel();
        $channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $queueName = str_replace('\\', '.', $commandClassName);
        $this->log->info('Subscribing Command Sink', ['queue' => $queueName]);

        $channel->queueDeclare(queue: $queueName, durable: true);
        $channel->queueBind(exchange: (string) $this->exchangeName, queue: $queueName, routingKey: $queueName);

        $this->consumers->add($queueName, async(function (Message $message, Channel $channel, Client $bunny) use (
            $commandClassName,
            $queueName,
            $sink,
        ): void {
            $commandId = CommandId::fromToken($this->header($message, 'message-id'));
            $correlationId = CorrelationId::fromToken($this->header($message, 'correlation-id'));
            $processId = ProcessId::fromToken($this->header($message, 'process-id'));
            $scope = new MessageScope($correlationId, CausationId::fromHex($commandId->hex()), $processId);

            try {
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
                $channel->nack($message, false, false);
                $this->log->error($t->getMessage(), ['queueName' => $queueName]);
            }
        }));
    }

    #[Override]
    public function run(): void
    {
        $this->consumption->untilShutdown();
        $this->consumers->subscribe($this->loop->channel());
        $this->loop->runUntilShutdown('CommandWorkers.Shutdown');
    }

    #[Override]
    public function runOnce(): void
    {
        $this->consumption->oneMessage();
        $this->consumers->subscribe($this->loop->channel());
        $this->loop->runUntilShutdown('CommandWorkers.Shutdown');
    }

    private function header(Message $message, string $name): string
    {
        if (!\is_scalar($message->headers[$name] ?? null)) {
            throw new UnexpectedValueException('Command message header must be scalar: ' . $name);
        }

        return (string) $message->headers[$name];
    }
}
