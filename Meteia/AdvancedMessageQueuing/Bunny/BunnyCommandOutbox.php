<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\MessageContext;
use Meteia\Commands\Command;
use Meteia\Commands\CommandDeliveries;
use Meteia\Commands\CommandDelivery;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandOutbox;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyCommandOutbox implements CommandOutbox, CommandDeliveries
{
    public function __construct(
        private BunnyChannels $channels,
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private MessageScopeSource $scopeSource,
    ) {}

    #[Override]
    public function publish(Command $command): void
    {
        $this->publishDelivery(new CommandDelivery(
            CommandId::random(),
            $command,
            $this->scopeSource->current(),
        ));
    }

    #[Override]
    public function publishDelivery(CommandDelivery $delivery): void
    {
        $channel = $this->channels->publishingChannel();
        $channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $command = $delivery->command();
        $queueName = str_replace('\\', '.', $command::class);
        $payload = $this->serializer->serialize($command, 'json');
        $context = MessageContext::fromScope($delivery->scope());
        $channel->publish(
            $payload,
            $context->headersWithMessageId((string) $delivery->commandId()),
            (string) $this->exchangeName,
            $queueName,
        );
        $this->log->info('Published Command', [
            'command' => $command::class,
            'exchange' => $this->exchangeName,
            'queue' => $queueName,
        ]);
    }
}
