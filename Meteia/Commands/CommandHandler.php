<?php

declare(strict_types=1);

namespace Meteia\Commands;

/**
 * @template TCommand of Command<mixed>
 * @template TResult
 */
interface CommandHandler
{
    /**
     * @param TCommand $command
     * @return TResult
     */
    public function handle(Command $command);
}
