<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Channel;
use Meteia\AdvancedMessageQueuing\Configuration\CommandsExchangeName;
use Meteia\AdvancedMessageQueuing\MessageContext;
use Meteia\Commands\Command;
use Meteia\Commands\CommandId;
use Meteia\Commands\CommandOutbox;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class BunnyCommandOutbox implements CommandOutbox
{
    public function __construct(
        private Channel $channel,
        private LoggerInterface $log,
        private CommandsExchangeName $exchangeName,
        private SerializerInterface $serializer,
        private MessageContext $context,
    ) {}

    #[\Override]
    public function publish(Command $command): void
    {
        $this->channel->exchangeDeclare((string) $this->exchangeName, durable: true);
        $queueName = str_replace('\\', '.', $command::class);
        $payload = $this->serializer->serialize($command, 'json');
        $this->channel->publish(
            $payload,
            $this->context->headersWithMessageId((string) CommandId::random()),
            $this->exchangeName,
            $queueName,
        );
        $this->log->info('Published Command', [
            'command' => $command::class,
            'exchange' => $this->exchangeName,
            'queue' => $queueName,
        ]);
    }
}
