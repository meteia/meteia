<?php

declare(strict_types=1);

namespace Meteia\Application;

/**
 * Where a command arrives to be processed. Implementing classes are use
 * cases — one per command. Named "Endpoint" not "Handler" per CLAUDE.md.
 *
 * @template TCommand of Command
 */
interface CommandEndpoint
{
    /**
     * @param TCommand $command
     */
    public function handle(Command $command): CommandResult;
}
