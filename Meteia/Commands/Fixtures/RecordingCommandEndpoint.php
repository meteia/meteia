<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\Accepted;
use Meteia\Commands\Command;
use Meteia\Commands\CommandEndpoint;
use Meteia\Commands\CommandResult;
use Override;

/**
 * @implements CommandEndpoint<ExampleCommand>
 */
final class RecordingCommandEndpoint implements CommandEndpoint
{
    /** @var list<Command> */
    public array $received = [];

    #[Override]
    public function handle(Command $command): CommandResult
    {
        $this->received[] = $command;

        return new Accepted();
    }
}
