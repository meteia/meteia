<?php

declare(strict_types=1);

namespace Meteia\Commands\Fixtures;

use Meteia\Commands\Command;
use Meteia\Commands\CommandOutbox;
use Override;

final class RecordingCommandOutbox implements CommandOutbox
{
    /** @var list<Command> */
    public array $published = [];

    #[Override]
    public function publish(Command $command): void
    {
        $this->published[] = $command;
    }
}
