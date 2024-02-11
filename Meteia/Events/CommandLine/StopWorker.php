<?php

declare(strict_types=1);

namespace Meteia\Events\CommandLine;

use Bunny\Channel;
use Meteia\CommandLine\Command;
use Symfony\Component\Console\Input\InputDefinition;

readonly class StopWorker implements Command
{
    public function __construct(private Channel $channel)
    {
    }

    #[\Override]
    public function execute(): void
    {
        $this->channel->publish('', [], 'EventWorkers.Shutdown');
    }

    #[\Override]
    public static function description(): string
    {
        return 'Stop the events worker queue.';
    }

    #[\Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }
}
