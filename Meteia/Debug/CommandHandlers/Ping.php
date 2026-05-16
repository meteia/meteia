<?php

declare(strict_types=1);

namespace Meteia\Debug\CommandHandlers;

use Bunny\Channel;
use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Meteia\Debug\Commands\Ping as DebugPing;
use Meteia\Debug\Events\Pong;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @implements CommandHandler<DebugPing>
 */
final readonly class Ping implements CommandHandler
{
    public function __construct(
        private Channel $channel,
        private SerializerInterface $serializer,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function handle(Command $command): void
    {
        \assert($command instanceof DebugPing, 'Debug ping handler only handles DebugPing commands');
        $replyTo = $command->replyTo;
        if ($replyTo === null) {
            return;
        }

        $pong = new Pong();
        $payload = $this->serializer->serialize($pong, 'json');

        $this->channel->publish($payload, [], '', $replyTo);

        $this->log->info('Replied with Pong via auto-generated queue', [
            'replyTo' => $replyTo,
        ]);
    }
}
