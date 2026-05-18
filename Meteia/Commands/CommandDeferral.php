<?php

declare(strict_types=1);

namespace Meteia\Commands;

interface CommandDeferral
{
    /**
     * @param Command<mixed> $command
     */
    public function defer(Command $command): DeferredCommand;
}
