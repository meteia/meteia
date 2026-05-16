<?php

declare(strict_types=1);

namespace Meteia\Debug\CommandSinks;

use Bunny\Channel;
use Meteia\Application\Accepted;
use Meteia\Application\Command;
use Meteia\Application\CommandEndpoint;
use Meteia\Application\CommandResult;
use Meteia\Debug\Commands\Ping as DebugPing;
use Meteia\Debug\Events\Pong;
use Meteia\Events\EventOutbox;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class Ping implements CommandEndpoint
{
    public function __construct(
        private Channel $channel,
        private SerializerInterface $serializer,
        private EventOutbox $eventOutbox,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function handle(Command $command): CommandResult
    {
        if ($command instanceof DebugPing && $command->replyTo !== null) {
            $pong = new Pong();
            $payload = $this->serializer->serialize($pong, 'json');

            $this->channel->publish($payload, [], '', $command->replyTo);

            $this->log->info('Replied with Pong via auto-generated queue', [
                'replyTo' => $command->replyTo,
            ]);
        }

        // Also publish via the normal event path so Events.Debug.Pong is a real observable event
        $this->eventOutbox->publish(new Pong());

        return new Accepted();
    }
}
