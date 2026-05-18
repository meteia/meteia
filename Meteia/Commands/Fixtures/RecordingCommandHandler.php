<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\Command;
use Meteia\Commands\CommandHandler;
use Override;

/**
 * @implements CommandHandler<ExampleCommand, void>
 */
final class RecordingCommandHandler implements CommandHandler
{
    /** @var list<Command<void>> */
    public array $received = [];

    #[Override]
    public function handle(Command $command): void
    {
        $this->received[] = $command;
    }
}
