<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandExecution
{
    /**
     * @template TResult
     * @param Command<TResult> $command
     * @return TResult
     */
    public function execute(Command $command): mixed;
}
