<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandHandlers
{
    /**
     * @template TResult
     * @param Command<TResult> $command
     * @return CommandHandler<Command<TResult>, TResult>
     */
    public function handlerFor(Command $command): CommandHandler;
}
