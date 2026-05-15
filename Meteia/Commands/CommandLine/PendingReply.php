<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use React\EventLoop\Loop;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

final readonly class PendingReply
{
    public function __construct(
        private string $replyQueue,
        private Channel $channel,
        private Client $client,
        private SerializerInterface $serializer,
        private OutputInterface $output,
    ) {}

    public function await(?string $expectedType = null): object
    {
        $this->output->writeln('<info>Waiting for reply on auto-generated queue ' . $this->replyQueue . ' ...</info>');

        $received = null;
        $type = $expectedType ?? stdClass::class;

        $this->channel->consume(
            function (Message $message, Channel $ch, Client $bunny) use (&$received, $type): void {
                try {
                    $received = $this->serializer->deserialize($message->content, $type, 'json');
                    $this->output->writeln(
                        '<info>Received reply ('
                        . (is_object($received) ? $received::class : gettype($received))
                        . ') on queue '
                        . $message->routingKey
                        . '</info>',
                    );
                } catch (Throwable $t) {
                    $this->output->writeln(
                        '<info>Received reply on auto-generated queue '
                        . $message->routingKey
                        . ' (deserialization as '
                        . $type
                        . ' failed)</info>',
                    );
                    $received = $message->content; // return raw content as fallback
                }

                $ch->ack($message);
                $bunny->disconnect();
                Loop::stop();
            },
            $this->replyQueue,
        );

        Loop::run();

        if ($received === null) {
            $this->output->writeln('<comment>No reply received (timeout or disconnect).</comment>');
            throw new RuntimeException('No reply received on queue ' . $this->replyQueue);
        }

        return $received;
    }
}
