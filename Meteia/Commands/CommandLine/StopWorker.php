<?php

declare(strict_types=1);

namespace Meteia\Commands\CommandLine;

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
        $this->channel->publish('', [], 'CommandWorkers.Shutdown');
    }

    #[\Override]
    public static function description(): string
    {
        return 'Stop the command worker queue.';
    }

    #[\Override]
    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }
}
