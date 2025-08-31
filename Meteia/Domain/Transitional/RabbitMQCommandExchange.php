<?php

declare(strict_types=1);

namespace Meteia\Domain\Transitional;

use Meteia\AdvancedMessageQueuing\Contracts\Exchange;
use Meteia\Commands\Command;
use Meteia\Domain\Contracts\PublishesCommands;
use Meteia\MessageStreams\MessageSerializer;

class RabbitMQCommandExchange implements PublishesCommands
{
    /** @var Exchange */
    private $exchange;

    /** @var MessageSerializer */
    private $messageSerializer;

    public function __construct(Exchange $exchange, MessageSerializer $messageSerializer)
    {
        $this->exchange = $exchange;
        $this->messageSerializer = $messageSerializer;
    }

    #[\Override]
    public function publish(Command $command): void
    {
        $routingKey = str_replace('\\', '.', $command::class);
        $body = $this->messageSerializer->serialize($command);
        $message = new RabbitMQCommandMessage($body, '');
        $this->exchange->publish($message, 'Meteia', $routingKey);
    }
}
